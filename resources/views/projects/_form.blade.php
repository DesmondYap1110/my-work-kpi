{{-- Shared add/edit form fields for projects --}}
@isset($project)
    @if ($project->status !== \App\Enums\ProjectStatus::Completed)
        <div class="row">
            <div class="col-lg-12">
                <div class="form-check form-switch form-switch-success" id="form-checkbox-div">
                    <input class="form-check-input" type="checkbox" role="switch" id="mark_complete" name="mark_complete" value="1">
                    <label class="form-check-label" for="mark_complete">Status</label>
                </div>
            </div>
        </div>
    @endif
@endisset
<div class="row">
    <div class="col-lg-6">
        <div class="input-group">
            <label>Title<span>*</span></label>
            <input type="text" class="form-control" name="title" maxlength="200" value="{{ old('title', $project->title ?? '') }}" required>
        </div>
    </div>
    {{-- No "added date" field: created_at already records when this was
         entered, and a second copy of the same fact is one that can disagree
         with it. --}}
    <div class="col-lg-6">
        <div class="input-group">
            <label>Start Date<span>*</span></label>
            <input type="date" class="form-control" name="start_date" value="{{ old('start_date', optional($project->start_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>End Date<span>*</span></label>
            <input type="date" class="form-control" name="end_date" value="{{ old('end_date', optional($project->end_date ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
</div>

{{-- No team field: work is assigned person by person on the project's own
     page, once it exists. --}}
