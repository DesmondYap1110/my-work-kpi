{{--
    One task row.

    Expects: $task, $project, $assignees, $tags
--}}
@php
    $rowId = 'task-'.$task->id;
    $editId = 'edit-task-'.$task->id;
@endphp

<div class="task-row @if ($task->is_milestone) is-milestone @endif" id="{{ $rowId }}">
    <div class="task-main">
        <div class="task-identity">
            <div>
                <span class="task-title">
                    @if ($task->is_milestone)
                        <i class="ri-flag-2-fill task-milestone-icon" title="Milestone"></i>
                    @endif
                    {{ $task->title }}
                </span>
                @if ($task->description)
                    <span class="task-description">{{ $task->description }}</span>
                @endif
                <span class="task-meta">
                    <i class="ri-user-3-line"></i>{{ $task->assignee->staff_name ?? 'Unassigned' }}
                    @if ($task->files->isNotEmpty())
                        <span class="task-files" title="{{ $task->files->count() }} attached">
                            <i class="ri-attachment-2"></i>{{ $task->files->count() }}
                        </span>
                    @endif
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
                <button type="button" class="tb-ac-btn" id="tb-ac-btn-1" title="Edit task"
                        data-inline-form="#{{ $editId }}" data-inline-hide="#{{ $rowId }}">
                    <i class="ri-edit-2-line"></i>
                </button>
                <form action="{{ route('project-tasks.destroy', $task->id) }}" method="POST"
                      class="d-inline js-confirm-delete"
                      data-confirm-title="Delete task"
                      data-confirm="Delete the task &quot;{{ $task->title }}&quot;?">
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
    @include('projects._task-fields', ['task' => $task])
    <div class="kpi-inline-actions">
        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save</button>
        <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"
           data-inline-restore="#{{ $rowId }}"><i class="ri-close-fill"></i>Cancel</a>
    </div>
</form>

{{-- One per attachment, outside the edit form above: their buttons sit inside
     it, and a form cannot contain another. --}}
@foreach ($task->files as $file)
    <form id="del-file-{{ $file->id }}" method="POST" class="js-confirm-delete"
          action="{{ route('project-tasks.attachments.destroy', [$task->id, $file->id]) }}"
          data-confirm-title="Remove attachment"
          data-confirm="Remove &quot;{{ $file->displayName() }}&quot; from this task? The file is deleted."
          data-confirm-label="Remove">
        @csrf @method('DELETE')
    </form>
@endforeach
