{{--
    The fields describing one task. Shared by the add and edit forms so the two
    cannot drift apart.

    Expects: $task (null when adding), $project, $assignees, $tags
--}}
<div class="row">
    <div class="col-lg-5">
        <div class="input-group">
            <label>Task<span>*</span></label>
            <input type="text" class="form-control" name="title" maxlength="255"
                   value="{{ $task?->title }}" placeholder="e.g. Build the checkout page" required>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="input-group">
            <label>Assignee</label>
            <select class="form-control" name="assignee_id">
                <option value="">Unassigned</option>
                @foreach ($assignees as $member)
                    <option value="{{ $member->id }}" @selected($task?->assignee_id === $member->id)>
                        {{ $member->staff_name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="input-group">
            <label>Status<span>*</span></label>
            <select class="form-control" name="status" required>
                @foreach (\App\Enums\TaskStatus::cases() as $status)
                    <option value="{{ $status->value }}"
                        @selected(($task?->status ?? \App\Enums\TaskStatus::ToDo) === $status)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="input-group">
            <label class="d-flex justify-content-between align-items-center">
                <span>Tag</span>
                {{-- The tag carries the points, so it is worth being able to
                     add a missing one without abandoning the task. --}}
                <a href="javascript:void(0);" class="quick-create-link"
                   data-quick-create-open="#quickCreateTag">
                    <i class="ri-add-line"></i>New Tag
                </a>
            </label>
            <select class="form-control" name="tag_id">
                <option value="">No tag &middot; scores nothing</option>
                @foreach ($tags as $tag)
                    <option value="{{ $tag->id }}" @selected($task?->tag_id === $tag->id)>
                        {{ $tag->name }} ({{ rtrim(rtrim(number_format((float) $tag->points, 2), '0'), '.') }} pts)
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="input-group">
            <label>Priority</label>
            <select class="form-control" name="priority">
                <option value="">None</option>
                @foreach (\App\Enums\TaskPriority::cases() as $priority)
                    <option value="{{ $priority->value }}" @selected($task?->priority === $priority)>
                        {{ $priority->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="input-group">
            <label>Start Date</label>
            <input type="date" class="form-control" name="start_date"
                   min="{{ $project->start_date->format('Y-m-d') }}"
                   max="{{ $project->end_date->format('Y-m-d') }}"
                   value="{{ $task?->start_date?->format('Y-m-d') }}">
        </div>
    </div>
    <div class="col-lg-3">
        <div class="input-group">
            <label>Due Date</label>
            <input type="date" class="form-control" name="due_date"
                   min="{{ $project->start_date->format('Y-m-d') }}"
                   max="{{ $project->end_date->format('Y-m-d') }}"
                   value="{{ $task?->due_date?->format('Y-m-d') }}">
        </div>
    </div>
    <div class="col-lg-12">
        <div class="form-check form-switch form-switch-success mt-1">
            <input class="form-check-input" type="checkbox" role="switch" value="1"
                   name="is_milestone" id="milestone-{{ $task?->id ?? 'new-'.($parentId ?? 'root') }}"
                   @checked($task?->is_milestone)>
            <label class="form-check-label" for="milestone-{{ $task?->id ?? 'new-'.($parentId ?? 'root') }}">
                Milestone &mdash; a checkpoint worth calling out, not just another task
            </label>
        </div>
    </div>
</div>
