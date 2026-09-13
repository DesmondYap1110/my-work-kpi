<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\AssessmentBand;
use App\Models\AssessmentRating;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * The appraisal form's own shape: which parts it has and what each is worth,
 * what the marks on its scale mean, and what a final percentage is called.
 *
 * Nothing here is fixed, because no two companies score people the same way.
 * One runs 50/50 soft skills and delivery, another 70/30; one rates out of 5,
 * another out of 10; one has six performance bands ending in "Poor", another
 * has three ending in "Terminate". So parts, marks and bands are all rows a
 * company adds, renames and removes - none of them are constants in code, and
 * the scoring reads the scale from the template rather than assuming it.
 *
 * Editing existing rows happens through update(), which saves the whole screen
 * at once. Adding and removing are separate actions per row, following the KPI
 * tree's inline pattern - see public/js/modules/inline-form.js.
 */
class AppraisalFormController extends Controller implements BreadcrumbInterfaces
{
    public function getBreadcrumbs(): array
    {
        return [
            ['name' => 'Appraisal', 'route' => 'appraisals.index', 'active' => false],
            ['name' => 'Form Setup', 'route' => '', 'active' => true],
        ];
    }

    public function edit(): View
    {
        $template = AssessmentTemplate::current();

        return view('appraisal-form.edit', [
            'template' => $template->load(['sections', 'ratings', 'bands']),
            'usedMarks' => $this->marksInUse($template),
            // An appraisal can be started from here too, not only from the
            // Appraisal list - see appraisals/_new-appraisal.
            'members' => Staff::active()->excludingAdmin()->with('position')->orderBy('staff_name')->get(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $template = AssessmentTemplate::current();

        $validated = $request->validate([
            'sections' => ['array'],
            'sections.*.title' => ['required', 'string', 'max:255'],
            'sections.*.weightage' => ['required', 'numeric', 'between:0,100'],

            'ratings' => ['array'],
            'ratings.*.value' => ['required', 'integer', 'between:0,100'],
            'ratings.*.label' => ['required', 'string', 'max:255'],
            'ratings.*.description' => ['nullable', 'string', 'max:1000'],

            'bands' => ['array'],
            'bands.*.min_score' => ['required', 'numeric', 'between:0,100'],
            'bands.*.max_score' => ['required', 'numeric', 'between:0,100'],
            'bands.*.label' => ['required', 'string', 'max:255'],
            'bands.*.outcome' => ['nullable', 'string', 'max:255'],
        ], [
            'sections.*.title.required' => 'Every part needs a name.',
            'sections.*.weightage.required' => 'Every part needs a weighting.',
            'ratings.*.label.required' => 'Every mark on the scale needs a name.',
            'ratings.*.value.required' => 'Every mark needs a number.',
            'bands.*.label.required' => 'Every band needs a name.',
        ]);

        if ($error = $this->inconsistency($validated)) {
            return back()->withInput()->withErrors(['form' => $error]);
        }

        // Scoped by template rather than updated by id alone: the ids come from
        // a form, and nothing here should be able to edit another template's
        // rows.
        DB::transaction(function () use ($template, $validated) {
            foreach ($validated['sections'] ?? [] as $id => $values) {
                AssessmentSection::where('template_id', $template->id)->whereKey($id)
                    ->update(['title' => $values['title'], 'weightage' => $values['weightage']]);
            }

            foreach ($validated['ratings'] ?? [] as $id => $values) {
                AssessmentRating::where('template_id', $template->id)->whereKey($id)
                    ->update([
                        'value' => $values['value'],
                        'label' => $values['label'],
                        'description' => $values['description'] ?? null,
                    ]);
            }

            foreach ($validated['bands'] ?? [] as $id => $values) {
                AssessmentBand::where('template_id', $template->id)->whereKey($id)
                    ->update([
                        'min_score' => $values['min_score'],
                        'max_score' => $values['max_score'],
                        'label' => $values['label'],
                        'outcome' => $values['outcome'] ?? null,
                    ]);
            }
        });

        return back()->with('status', 'Appraisal form updated successfully.');
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $template = AssessmentTemplate::current();

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in([AssessmentSection::TYPE_RATING, AssessmentSection::TYPE_PROJECT])],
            'weightage' => ['required', 'numeric', 'between:0,100'],
        ]);

        // There is one source of project rows - the member's tasks in the
        // review period - so a second project part would show the same rows
        // again and score them twice, once under each part. Rating parts have
        // no such limit: each draws from its own categories.
        if ($validated['type'] === AssessmentSection::TYPE_PROJECT && $this->hasProjectSection($template)) {
            return back()->withErrors([
                'form' => 'The form already has a part scored from project work, and there is only one set of project rows to score. Add a part rated from KPI categories instead.',
            ]);
        }

        AssessmentSection::create([
            'template_id' => $template->id,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'weightage' => $validated['weightage'],
            // Only one way to score a project part so far: the member's tasks
            // falling in the review period.
            'calculation' => $validated['type'] === AssessmentSection::TYPE_PROJECT ? 'tasks_in_period' : null,
            'sort_order' => ((int) $template->sections()->max('sort_order')) + 1,
        ]);

        return back()->with('status', $validated['type'] === AssessmentSection::TYPE_RATING
            ? 'Part added. Point some KPI categories at it, or it will have nothing to rate.'
            : 'Part added successfully.');
    }

    public function destroySection(AssessmentSection $section): RedirectResponse
    {
        $this->refuseForeign($section->template_id);

        // A form with no rating part has nothing to rate; the headings pointed
        // at it would have nowhere to go.
        $ratingParts = AssessmentSection::where('template_id', $section->template_id)
            ->where('type', AssessmentSection::TYPE_RATING)
            ->count();

        if ($section->type === AssessmentSection::TYPE_RATING && $ratingParts <= 1) {
            return back()->withErrors(['form' => 'This is the only part with measurements in it. Add another before removing this one.']);
        }

        $moved = $section->categories()->count();
        $section->delete();

        return back()->with('status', $moved > 0
            ? 'Part removed. Its '.$moved.' '.\Illuminate\Support\Str::plural('category', $moved).' moved to the first remaining part.'
            : 'Part removed successfully.');
    }

    public function storeRating(Request $request): RedirectResponse
    {
        $template = AssessmentTemplate::current();

        $validated = $request->validate([
            'value' => ['required', 'integer', 'between:0,100',
                Rule::unique('assessment_rating', 'value')->where('template_id', $template->id)],
            'label' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'value.unique' => 'That mark is already on the scale.',
        ]);

        AssessmentRating::create($validated + ['template_id' => $template->id]);

        return back()->with('status', 'Mark added to the scale.');
    }

    public function destroyRating(AssessmentRating $rating): RedirectResponse
    {
        $this->refuseForeign($rating->template_id);

        if ($rating->template->ratings()->count() <= 1) {
            return back()->withErrors(['form' => 'A form needs at least one mark on its scale.']);
        }

        // An appraisal already scored with this mark would otherwise show a
        // number with no meaning next to it.
        if (in_array($rating->value, $this->marksInUse($rating->template), true)) {
            return back()->withErrors(['form' => 'Appraisals have already been scored with this mark, so it cannot be removed. Rename it instead.']);
        }

        $rating->delete();

        return back()->with('status', 'Mark removed from the scale.');
    }

    public function storeBand(Request $request): RedirectResponse
    {
        $template = AssessmentTemplate::current();

        $validated = $request->validate([
            'min_score' => ['required', 'numeric', 'between:0,100'],
            'max_score' => ['required', 'numeric', 'between:0,100', 'gte:min_score'],
            'label' => ['required', 'string', 'max:255'],
            'outcome' => ['nullable', 'string', 'max:255'],
        ], [
            'max_score.gte' => 'A band cannot end below where it starts.',
        ]);

        AssessmentBand::create($validated + ['template_id' => $template->id]);

        return back()->with('status', 'Band added successfully.');
    }

    public function destroyBand(AssessmentBand $band): RedirectResponse
    {
        $this->refuseForeign($band->template_id);

        $band->delete();

        return back()->with('status', 'Band removed successfully.');
    }

    /**
     * Checks the screen holds together as a whole, which per-field rules
     * cannot see.
     *
     * @param  array<string, mixed>  $validated
     */
    private function inconsistency(array $validated): ?string
    {
        foreach ($validated['bands'] ?? [] as $values) {
            if ((float) $values['max_score'] < (float) $values['min_score']) {
                return 'A band cannot end below where it starts.';
            }
        }

        $values = array_column($validated['ratings'] ?? [], 'value');

        if (count($values) !== count(array_unique($values))) {
            return 'Two marks on the scale cannot share the same number.';
        }

        $weights = array_sum(array_column($validated['sections'] ?? [], 'weightage'));

        if ($weights <= 0 && ! empty($validated['sections'])) {
            return 'At least one part must carry some weight, or nothing can be scored.';
        }

        return null;
    }

    /**
     * Marks that appraisals have already been scored with, so the scale cannot
     * quietly lose them.
     *
     * @return array<int, int>
     */
    private function marksInUse(AssessmentTemplate $template): array
    {
        $ids = $template->assessments()->pluck('id');

        if ($ids->isEmpty()) {
            return [];
        }

        $from = fn (string $table) => DB::table($table)
            ->whereIn('assessment_id', $ids)
            ->select('employee_score', 'reviewer_score')
            ->get()
            ->flatMap(fn ($row) => [$row->employee_score, $row->reviewer_score]);

        return $from('assessment_score')
            ->merge($from('assessment_project_score'))
            ->filter(fn ($mark) => $mark !== null)
            ->map(fn ($mark) => (int) $mark)
            ->unique()
            ->values()
            ->all();
    }

    private function hasProjectSection(AssessmentTemplate $template): bool
    {
        return AssessmentSection::where('template_id', $template->id)
            ->where('type', AssessmentSection::TYPE_PROJECT)
            ->exists();
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
