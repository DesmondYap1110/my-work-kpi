<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\AssessmentBand;
use App\Models\AssessmentTemplate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Settings > Project Form Setup: the performance bands - what a final KPI score is
 * called, and what it means for the review.
 *
 * Nothing here is fixed, because no two companies describe performance the
 * same way: one has six bands ending in "Poor", another three ending in
 * "Terminate". Bands are rows added, edited and removed inline - see
 * App\Components\Datatables\PerformanceBandList. How a score splits between
 * projects and KPI objectives, and the marks each item is rated with, are set
 * per position on its KPI Setting page.
 */
class AppraisalFormController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Settings', 'route' => '', 'active' => false],
            ['name' => 'Project Form Setup', 'route' => '', 'active' => true],
        ];
    }

    public function edit(): View
    {
        return view('appraisal-form.edit');
    }

    public function storeBand(Request $request): RedirectResponse|JsonResponse
    {
        $template = AssessmentTemplate::current();
        $validated = $this->validateBand($request, $template);

        AssessmentBand::create($validated + ['template_id' => $template->id]);

        return $request->expectsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('status', 'Band added successfully.');
    }

    public function updateBand(Request $request, AssessmentBand $band): RedirectResponse|JsonResponse
    {
        $this->refuseForeign($band->template_id);

        $band->update($this->validateBand($request, $band->template, $band));

        return $request->expectsJson()
            ? response()->json(['status' => 'ok'])
            : back()->with('status', 'Band updated successfully.');
    }

    public function destroyBand(AssessmentBand $band): RedirectResponse
    {
        $this->refuseForeign($band->template_id);

        $band->delete();

        return back()->with('status', 'Band removed successfully.');
    }

    /**
     * A band's own rules, plus the one the table as a whole needs: two bands
     * may not cover the same score, or a score would have two names.
     *
     * @return array{min_score: float, max_score: float, label: string, outcome: string|null}
     */
    private function validateBand(Request $request, AssessmentTemplate $template, ?AssessmentBand $ignore = null): array
    {
        $validated = $request->validate([
            'min_score' => ['required', 'numeric', 'between:0,100'],
            'max_score' => ['required', 'numeric', 'between:0,100', 'gte:min_score'],
            'label' => ['required', 'string', 'max:255'],
            'outcome' => ['nullable', 'string', 'max:255'],
        ], [
            'min_score.required' => 'Give the band a starting %.',
            'max_score.required' => 'Give the band an ending %.',
            'max_score.gte' => 'A band cannot end below where it starts.',
            'label.required' => 'Every band needs a name.',
        ]);

        $overlap = AssessmentBand::query()
            ->where('template_id', $template->id)
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->id))
            ->where('min_score', '<=', $validated['max_score'])
            ->where('max_score', '>=', $validated['min_score'])
            ->first();

        if ($overlap) {
            throw ValidationException::withMessages([
                'min_score' => 'This range overlaps "'.$overlap->label.'" ('
                    .(float) $overlap->min_score.' - '.(float) $overlap->max_score.'%).',
            ]);
        }

        return $validated;
    }

    /**
     * Rows arrive by id from a form, so each one is checked against the
     * template in use rather than trusted.
     */
    private function refuseForeign(?int $templateId): void
    {
        abort_unless($templateId === AssessmentTemplate::current()->id, 404);
    }
}
