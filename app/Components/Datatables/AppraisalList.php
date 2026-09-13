<?php

namespace App\Components\Datatables;

use App\Components\Filters\DateFilter;
use App\Components\Filters\SelectFilter;
use App\Enums\AssessmentStatus;
use App\Models\Assessment;
use App\Models\Staff;
use App\Models\StaffPosition;
use App\Models\Team;
use App\Queries\AppraisalListQuery;
use App\Services\AssessmentScoreService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Every appraisal: who is being reviewed, for what period, and whether they
 * have been given it yet.
 */
class AppraisalList extends Datatables
{
    public function __construct(private readonly AssessmentScoreService $scores)
    {
    }

    public static function getTableColumns(): array
    {
        return [
            'member' => 'Member',
            'period' => 'Review Period',
            'review_date' => 'Reviewed',
            'score' => 'Score',
            'status' => 'Status',
            'action' => 'Actions',
        ];
    }

    public function filters(): array
    {
        $statuses = collect(AssessmentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])->all();

        return [
            new SelectFilter('staff_id', 'Member', Staff::excludingAdmin()->orderBy('staff_name')->pluck('staff_name', 'id')->all()),
            new SelectFilter('team_id', 'Team', Team::orderBy('team_name')->pluck('team_name', 'id')->all()),
            new SelectFilter('position_id', 'Position', StaffPosition::orderBy('position_name')->pluck('position_name', 'id')->all()),
            new SelectFilter('status', 'Status', $statuses),
            new DateFilter('period_from', 'Period From'),
            new DateFilter('period_to', 'Period To'),
        ];
    }

    public function centeredColumns(): array
    {
        return ['period', 'review_date', 'score', 'status'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(AppraisalListQuery::class)->forRequest($request),
            $request,
            // Rendered from relations and from a calculation, so there is no
            // column on assessment to sort either by.
            ['member' => null, 'period' => 'period_from', 'score' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $rows = $result->getCollection()->map(function (Assessment $appraisal) {
            return [
                'member' => $this->memberCell($appraisal),
                'period' => e($appraisal->periodLabel()),
                'review_date' => $appraisal->review_date?->format('d M Y') ?? '-',
                'score' => $this->scoreCell($appraisal),
                'status' => $this->tbStatus($appraisal->status->label(), $appraisal->status->colourId()),
                'action' => $this->actionButtons($appraisal),
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * The name opens the form, the same way a project's title opens its tasks.
     */
    private function memberCell(Assessment $appraisal): string
    {
        $name = $this->tbTextLink(
            route('appraisals.show', $appraisal->id),
            $appraisal->staff->staff_name ?? 'Unknown',
            'Open the review form'
        );

        return $name.'<span class="kpi-item-desc">'.e($appraisal->position->position_name ?? 'No position')
            .' &middot; '.e($appraisal->staff->team->team_name ?? 'No team').'</span>';
    }

    /**
     * A draft that has not been rated yet reads "-", not 0% - the difference
     * between "scored badly" and "not scored" matters most on this list.
     */
    private function scoreCell(Assessment $appraisal): string
    {
        $summary = $this->scores->summary($appraisal);

        if ($summary['percentage'] === null) {
            return '-';
        }

        $cell = '<strong>'.$summary['percentage'].'%</strong>';

        if ($summary['band']) {
            $cell .= '<span class="kpi-item-desc">'.e($summary['band']->label)
                .($summary['band']->outcome ? ' &middot; '.e($summary['band']->outcome) : '').'</span>';
        }

        return $cell;
    }

    private function actionButtons(Assessment $appraisal): string
    {
        return implode(' ', [
            $this->tbLink(route('appraisals.show', $appraisal->id), 'ri-file-list-3-line', 'tb-ac-btn-4', 'Open the review form'),
            $this->tbDeleteForm(route('appraisals.destroy', $appraisal->id)),
        ]);
    }
}
