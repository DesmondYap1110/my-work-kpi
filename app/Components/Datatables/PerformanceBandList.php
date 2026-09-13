<?php

namespace App\Components\Datatables;

use App\Models\AssessmentBand;
use App\Models\AssessmentTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Performance bands - what a final KPI score is called and what it means for
 * the review - added, edited and removed inline, like Project Tag Setting.
 */
class PerformanceBandList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'min_score' => 'From %',
            'max_score' => 'To %',
            'label' => 'Band',
            'outcome' => 'Outcome',
            'action' => 'Actions',
        ];
    }

    public function inlineFields(): array
    {
        return [
            'min_score' => ['type' => 'number', 'required' => true, 'placeholder' => 'e.g. 70'],
            'max_score' => ['type' => 'number', 'required' => true, 'placeholder' => 'e.g. 79.99'],
            'label' => ['type' => 'text', 'required' => true, 'placeholder' => 'e.g. Good'],
            'outcome' => ['type' => 'text', 'required' => false, 'placeholder' => 'Pass / Extend / Fail'],
        ];
    }

    public function inlineRoutes(): array
    {
        return [
            'store' => route('appraisal-form.bands.store'),
            'update' => route('appraisal-form.bands.update', '__id__'),
        ];
    }

    public function centeredColumns(): array
    {
        return ['min_score', 'max_score', 'outcome'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            AssessmentBand::query()
                ->where('template_id', AssessmentTemplate::current()->id)
                ->orderByDesc('min_score'),
            $request,
            []
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        $trim = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');

        $rows = $result->getCollection()->map(fn (AssessmentBand $band) => [
            'min_score' => $trim($band->min_score),
            'max_score' => $trim($band->max_score),
            'label' => e($band->label),
            'outcome' => $band->outcome ? $this->tbStatus($band->outcome, $this->outcomeColour($band->outcome)) : '-',
            'action' => $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', ['id' => $band->id], 'js-inline-editable')
                .' '.$this->tbDeleteForm(route('appraisal-form.bands.destroy', $band->id)),
            '_inline' => [
                'min_score' => (float) $band->min_score,
                'max_score' => (float) $band->max_score,
                'label' => $band->label,
                'outcome' => $band->outcome,
            ],
        ])->all();

        return $this->respond($request, $result, $rows);
    }

    /**
     * Pill colour for the common outcomes; anything a company names itself is
     * shown neutral.
     */
    private function outcomeColour(string $outcome): int
    {
        return match (strtolower(trim($outcome))) {
            'pass' => 1,
            'fail' => 2,
            'extend' => 3,
            default => 4,
        };
    }
}
