{{--
    Shared add/edit fields for projects: the Add Project dialog on the list, and
    the Edit Project dialog on the project's own page.

    On an edit, old input is only used when it came back from THIS form (the
    hidden _form marker). The project page has task forms with their own
    start_date, and a failed task save must not leak its dates in here.
--}}
@php
    $fromOld = ! isset($project) || old('_form') === 'edit_project';
    $value = fn (string $field, $current) => $fromOld ? old($field, $current) : $current;
@endphp

@isset($project)
    <input type="hidden" name="_form" value="edit_project">

    @if ($project->status !== \App\Enums\ProjectStatus::Completed)
        <div class="row">
            <div class="col-lg-12">
                <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                    <input class="form-check-input" type="checkbox" role="switch" id="mark_complete" name="mark_complete" value="1">
                    {{-- Completing a project sets up KPI records for everyone
                         with a task on it, so it says what it does. --}}
                    <label class="form-check-label" for="mark_complete">Mark as completed</label>
                </div>
            </div>
        </div>
    @endif
@endisset
<div class="row">
    <div class="col-lg-12">
        <div class="input-group">
            <label>Title<span>*</span></label>
            <input type="text" class="form-control" name="title" maxlength="200" value="{{ $value('title', $project->title ?? '') }}" required>
        </div>
    </div>
    {{-- No "added date" field: created_at already records when this was
         entered, and a second copy of the same fact is one that can disagree
         with it. --}}
    <div class="col-lg-6">
        <div class="input-group">
            <label>Start Date<span>*</span></label>
            <input type="date" class="form-control" name="start_date" value="{{ $value('start_date', optional($project->start_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>End Date<span>*</span></label>
            <input type="date" class="form-control" name="end_date" value="{{ $value('end_date', optional($project->end_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
</div>

{{-- No team field: work is assigned person by person on the project's own
     page, once it exists. --}}
