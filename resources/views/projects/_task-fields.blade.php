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
    {{-- A tag carries the points a delivery score is made of, so only the
         administrator sets one. Members see what it is worth, not a way to
         change it - and the field is dropped server-side too, so hiding it
         here is presentation rather than the rule. See
         App\Http\Requests\Concerns\OnlyAdminAssignsTags. --}}
    <div class="col-lg-3">
        <div class="input-group">
            @if (auth()->user()->isAdmin())
                <label class="d-flex justify-content-between align-items-center">
                    <span>Tag</span>
                    {{-- Worth being able to add a missing one without
                         abandoning the task. --}}
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
            @else
                <label>Tag</label>
                <input type="text" class="form-control" readonly
                       value="{{ $task?->tag
                            ? $task->tag->name.' · '.rtrim(rtrim(number_format((float) $task->tag->points, 2), '0'), '.').' pts'
                            : 'Set by the administrator' }}">
            @endif
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
    {{-- The title is a label; this is the brief. It is also what an appraiser
         reads when Part 2 of a review lists this work months later, so it is
         worth writing properly. --}}
    <div class="col-lg-12">
        <div class="input-group">
            <label>Description</label>
            <textarea class="form-control" name="description" rows="6" maxlength="5000"
                      placeholder="What needs doing, and anything the next person needs to know.">{{ $task?->description }}</textarea>
        </div>
    </div>

    {{-- Half width: a file picker is one short control, and stretched across
         the page it read as a long empty bar. What is already attached sits in
         the other half, on an edit. --}}
    <div class="col-lg-6">
        <div class="input-group">
            <label>Attachments</label>
            <input type="file" class="form-control" name="attachments[]" multiple
                   accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip">
            <span id="note-p" class="d-block task-file-note">
                Up to 10 files, 10&nbsp;MB each. Adds to what is already attached.
            </span>
        </div>
    </div>

    @if ($task?->files->isNotEmpty())
        <div class="col-lg-6">
            <label>Attached</label>
            <ul class="task-file-list">
                @foreach ($task->files as $file)
                    <li>
                        <a href="{{ $file->url() }}" target="_blank" rel="noopener">
                            <i class="ri-attachment-2"></i>{{ $file->displayName() }}
                        </a>
                        <span class="kpi-item-desc">
                            {{ $file->uploaded_at->format('d M Y H:i') }}
                            @if ($file->staff) &middot; {{ $file->staff->staff_name }} @endif
                        </span>
                        {{-- Its own form, so it cannot be nested in the task
                             form around it - reached by the `form` attribute,
                             the same way the appraisal setup rows are. --}}
                        <button type="submit" form="del-file-{{ $file->id }}"
                                class="tb-ac-btn" id="tb-ac-btn-2" title="Remove attachment">
                            <i class="ri-delete-bin-6-line"></i>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="col-lg-12">
        <div class="form-check form-switch form-switch-success mt-1">
            <input class="form-check-input" type="checkbox" role="switch" value="1"
                   name="is_milestone" id="milestone-{{ $task?->id ?? 'new' }}"
                   @checked($task?->is_milestone)>
            <label class="form-check-label" for="milestone-{{ $task?->id ?? 'new' }}">
                Milestone &mdash; a checkpoint worth calling out, not just another task
            </label>
        </div>
    </div>
</div>
