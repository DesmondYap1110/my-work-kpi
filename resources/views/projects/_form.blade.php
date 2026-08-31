{{-- Shared add/edit form fields for projects --}}
@isset($project)
    @if ($project->p_status !== \App\Enums\ProjectStatus::Completed)
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
            <input type="text" class="form-control" name="p_Title" maxlength="200" value="{{ old('p_Title', $project->p_Title ?? '') }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Date<span>*</span></label>
            <input type="date" class="form-control" name="p_addDate" value="{{ old('p_addDate', optional($project->p_addDate ?? null)->format('Y-m-d') ?? now()->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Start Date<span>*</span></label>
            <input type="date" class="form-control" name="p_SDate" value="{{ old('p_SDate', optional($project->p_SDate ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>End Date<span>*</span></label>
            <input type="date" class="form-control" name="p_EDate" value="{{ old('p_EDate', optional($project->p_EDate ?? null)->format('Y-m-d')) }}" required>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Team<span>*</span></label>
            <select class="form-control" name="team_id" required>
                <option value="" disabled @selected(is_null(old('team_id', $project->team_id ?? null)))>Select Team</option>
                @foreach ($teams as $team)
                    <option value="{{ $team->team_id }}" @selected((int) old('team_id', $project->team_id ?? null) === $team->team_id)>{{ $team->team_name }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
