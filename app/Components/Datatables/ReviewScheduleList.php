<?php

namespace App\Components\Datatables;

use App\Components\Filters\SelectFilter;
use App\Components\Filters\TextFilter;
use App\Enums\AppraisalCycle;
use App\Models\Team;
use App\Services\AppraisalScheduleService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Illuminate\Support\Str;

/**
 * Review Schedule: every member's review cycle and when their next appraisal
 * is due, paged and filterable so it stays usable with many members and teams.
 *
 * Due dates are worked out in PHP (see AppraisalScheduleService), not stored,
 * so the rows are built for the filtered members and paged here rather than by
 * the database. That is fine at company scale - hundreds of members - and
 * keeps the due date from ever going stale.
 */
class ReviewScheduleList extends Datatables
{
    public const STATES = [
        'overdue' => 'Overdue',
        'soon' => 'Due soon',
        'scheduled' => 'Scheduled',
        'manual' => 'Manual',
        'draft' => 'Draft open',
    ];

    public function __construct(private readonly AppraisalScheduleService $schedule)
    {
    }

    public static function getTableColumns(): array
    {
        return [
            'member' => 'Member',
            'team' => 'Team',
            'cycle' => 'Review every',
            'last' => 'Last appraisal',
            'due' => 'Next due',
            'action' => 'Actions',
        ];
    }

    public function filters(): array
    {
        return [
            new TextFilter('q', 'Member', 'Search name'),
            new SelectFilter('team_id', 'Team', Team::orderBy('team_name')->pluck('team_name', 'id')->all()),
            new SelectFilter('state', 'Status', self::STATES),
        ];
    }

    /**
     * Opens with whoever is most overdue at the top.
     */
    public function defaultOrder(): ?array
    {
        return ['due', 'asc'];
    }

    public function centeredColumns(): array
    {
        return ['team', 'cycle', 'last', 'due'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        $rows = $this->schedule->rows(function ($query) use ($request) {
            $query->when($request->filled('q'), fn ($q) => $q->where('staff_name', 'like', '%'.$request->input('q').'%'))
                ->when($request->filled('team_id'), fn ($q) => $q->where('team_id', $request->integer('team_id')));
        });

        if ($request->filled('state')) {
            $state = $request->input('state');
            $rows = $rows->filter(fn ($row) => $state === 'draft' ? $row['draft'] !== null : $row['state'] === $state)->values();
        }

        $rows = $this->sort($rows, $request);

        $length = max(1, min(100, $request->integer('length', static::PAGINATION_NUMBER)));
        $page = intdiv($request->integer('start', 0), $length) + 1;

        return new Paginator($rows->forPage($page, $length)->values(), $rows->count(), $length, $page);
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $cycles = AppraisalCycle::cases();

        $rows = $result->getCollection()->map(function (array $row) use ($cycles) {
            $staff = $row['staff'];

            return [
                'member' => e($staff->staff_name)
                    .'<span class="kpi-item-desc">'.e($staff->position->position_name ?? '-').'</span>'
                    .($row['draft'] ? ' <span class="tb-status" id="tb-status-3">Draft open</span>' : ''),
                'team' => e($staff->team->team_name ?? '-'),
                'cycle' => $this->cycleSelect($staff, $row['cycle'], $cycles),
                'last' => $row['last']
                    ? '<a href="'.route('appraisals.show', $row['last']->id).'" class="tb-date-link" title="Open this appraisal">'.e($row['last']->periodLabel()).'</a>'
                    : '<span class="kpi-muted">Never</span>',
                'due' => $this->dueCell($row),
                'action' => $this->actionCell($row),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * Soonest due first by default, Manual members last. Member and team sort
     * by name; the other columns follow the due date.
     */
    private function sort($rows, Request $request)
    {
        $columns = array_keys(static::getTableColumns());
        $order = (array) $request->input('order', []);
        $column = $columns[$order[0]['column'] ?? -1] ?? 'due';
        $desc = ($order[0]['dir'] ?? 'asc') === 'desc';

        $key = match ($column) {
            'member' => fn ($row) => Str::lower($row['staff']->staff_name),
            'team' => fn ($row) => Str::lower($row['staff']->team->team_name ?? '~'),
            default => fn ($row) => $row['due'] ? $row['due']->timestamp : PHP_INT_MAX,
        };

        return ($desc ? $rows->sortByDesc($key) : $rows->sortBy($key))->values();
    }

    private function cycleSelect($staff, AppraisalCycle $current, array $cycles): string
    {
        $options = collect($cycles)->map(fn (AppraisalCycle $cycle) => '<option value="'.$cycle->value.'"'
            .($cycle === $current ? ' selected' : '').'>'.e($cycle->label()).'</option>')->implode('');

        // Saves as soon as a cycle is picked.
        return '<form action="'.route('appraisals.cycle.update', $staff->id).'" method="POST" class="d-inline">'
            .csrf_field().method_field('PUT')
            .'<select class="form-control appraisal-cycle-select" name="appraisal_cycle" onchange="this.form.submit()"'
            .' aria-label="How often '.e($staff->staff_name).' is appraised">'.$options.'</select></form>';
    }

    private function dueCell(array $row): string
    {
        if ($row['state'] === 'manual') {
            return '<span class="kpi-muted">-</span>';
        }

        $date = $row['due']->format('d M Y');
        $days = $row['days'];

        return match ($row['state']) {
            'overdue' => $date.' '.$this->tbStatus('Overdue '.abs($days).' '.Str::plural('day', abs($days)), 2),
            'soon' => $date.' '.$this->tbStatus($days === 0 ? 'Due today' : 'Due in '.$days.' '.Str::plural('day', $days), 3),
            default => $date.' <span class="kpi-item-desc">in '.$days.' days</span>',
        };
    }

    private function actionCell(array $row): string
    {
        $staff = $row['staff'];

        // One draft at a time: continue it rather than start another.
        if ($row['draft']) {
            return $this->tbLink(route('appraisals.show', $row['draft']->id), 'ri-draft-line', 'tb-ac-btn-3',
                'Continue '.$staff->staff_name.'\'s draft ('.$row['draft']->periodLabel().')');
        }

        // Opens New Appraisal with this member already chosen.
        return '<button type="button" class="tb-ac-btn" id="tb-ac-btn-6" title="Start appraisal for '.e($staff->staff_name).'"'
            .' data-bs-toggle="modal" data-bs-target="#addAppraisalModal"'
            .' onclick="var s = document.querySelector(\'#addAppraisalModal [name=staff_id]\'); s.value = \''.$staff->id.'\'; s.dispatchEvent(new Event(\'change\'))">'
            .'<i class="ri-survey-line"></i></button>';
    }
}
