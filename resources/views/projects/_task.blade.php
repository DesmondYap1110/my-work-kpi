{{--
    One task row, with its subtasks nested underneath.

    Expects: $task, $project, $assignees, $tags
--}}
@php
    $rowId = 'task-'.$task->id;
    $editId = 'edit-task-'.$task->id;
    $addSubId = 'add-subtask-'.$task->id;
    $bodyId = 'body-task-'.$task->id;
    $children = $task->children;
    $isSubtask = $task->parent_id !== null;

    $confirm = $children->count() > 0
        ? 'Delete "'.$task->title.'"? This also removes its '.$children->count().' '
            .Str::plural('subtask', $children->count()).'.'
        : 'Delete the task "'.$task->title.'"?';
@endphp

<div class="task-row @if ($task->is_milestone) is-milestone @endif" id="{{ $rowId }}">
    <div class="task-main">
        <div class="task-identity">
            @if (! $isSubtask)
                <button type="button" class="tb-ac-btn tb-ac-toggle" title="Show subtasks"
                        aria-expanded="false" aria-controls="{{ $bodyId }}"
                        data-collapse="#{{ $bodyId }}">
                    <i class="ri-arrow-down-s-line"></i>
                </button>
            @endif

            <div>
                <span class="task-title">
                    @if ($task->is_milestone)
                        <i class="ri-flag-2-fill task-milestone-icon" title="Milestone"></i>
                    @endif
                    {{ $task->title }}
                    @if ($children->count() > 0)
                        <span class="kpi-count">{{ $children->count() }}</span>
                    @endif
                </span>
                <span class="task-meta">
                    <i class="ri-user-3-line"></i>{{ $task->assignee->staff_name ?? 'Unassigned' }}
                    @if ($task->due_date)
                        <span class="task-due @if ($task->isOverdue()) is-overdue @endif">
                            <i class="ri-calendar-line"></i>{{ $task->due_date->format('d M Y') }}
                        </span>
                    @endif
                    @if ($task->priority)
                        <span class="tb-status" id="tb-status-{{ $task->priority->colourId() }}">
                            {{ $task->priority->label() }}
                        </span>
                    @endif
                </span>
            </div>
        </div>

        <div class="task-controls">
            @if ($task->tag)
                <span class="task-points" title="Points earned when this is done">
                    {{ $task->tag->name }} &middot;
                    {{ rtrim(rtrim(number_format((float) $task->tag->points, 2), '0'), '.') }}
                </span>
            @endif

            {{-- Changes status on its own - see public/js/modules/status-select.js --}}
            <select class="form-control task-status js-task-status"
                    data-url="{{ route('project-tasks.status', $task->id) }}"
                    data-row="#{{ $rowId }}" aria-label="Task status">
                @foreach (\App\Enums\TaskStatus::cases() as $status)
                    <option value="{{ $status->value }}" @selected($task->status === $status)>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            <span class="task-actions">
                @if (! $isSubtask)
                    <button type="button" class="tb-ac-btn" id="tb-ac-btn-6" title="Add subtask"
                            data-inline-form="#{{ $addSubId }}">
                        <i class="ri-add-line"></i>
                    </button>
                @endif
                <button type="button" class="tb-ac-btn" id="tb-ac-btn-1" title="Edit task"
                        data-inline-form="#{{ $editId }}" data-inline-hide="#{{ $rowId }}">
                    <i class="ri-edit-2-line"></i>
                </button>
                <form action="{{ route('project-tasks.destroy', $task->id) }}" method="POST"
                      class="d-inline js-confirm-delete"
                      data-confirm-title="Delete task"
                      data-confirm="{{ $confirm }}">
                    @csrf @method('DELETE')
                    <button type="submit" class="tb-ac-btn" id="tb-ac-btn-2" title="Delete task">
                        <i class="ri-delete-bin-6-line"></i>
                    </button>
                </form>
            </span>
        </div>
    </div>
</div>

{{-- Edit, in place of the row above --}}
<form class="js-inline-form kpi-inline-form" id="{{ $editId }}" method="POST"
      action="{{ route('project-tasks.update', $task->id) }}" enctype="multipart/form-data" hidden>
    @csrf @method('PUT')
    <input type="hidden" name="project_id" value="{{ $project->id }}">
    @if ($task->parent_id)
        <input type="hidden" name="parent_id" value="{{ $task->parent_id }}">
    @endif
    @include('projects._task-fields', ['task' => $task, 'parentId' => $task->parent_id])
    <div class="kpi-inline-actions">
        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save</button>
        <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"
           data-inline-restore="#{{ $rowId }}"><i class="ri-close-fill"></i>Cancel</a>
    </div>
</form>

@if (! $isSubtask)
    {{-- Closed by default - see public/js/modules/collapse.js --}}
    <div class="js-collapse task-children" id="{{ $bodyId }}" hidden>
        @forelse ($children as $child)
            @include('projects._task', ['task' => $child])
        @empty
            <p class="kpi-empty mb-0">No subtasks yet.</p>
        @endforelse

        <form class="js-inline-form kpi-inline-form" id="{{ $addSubId }}" method="POST"
              action="{{ route('project-tasks.store') }}" enctype="multipart/form-data" hidden>
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            <input type="hidden" name="parent_id" value="{{ $task->id }}">
            @include('projects._task-fields', ['task' => null, 'parentId' => $task->id])
            <div class="kpi-inline-actions">
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Subtask</button>
                <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
            </div>
        </form>
    </div>
@endif
