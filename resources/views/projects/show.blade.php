@extends('layouts.app')

@section('title', $project->title)

@section('content')
    @php
        $statuses = \App\Enums\TaskStatus::cases();
        $done = $tasksByStatus->get(\App\Enums\TaskStatus::Done->value, collect())->count();
    @endphp

    <div id="tb-box" class="general-box mb-3">
        <div id="table-padding" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <p id="tb-title" class="mb-1">{{ $project->title }}</p>
                <p id="footer-p" class="mb-0">
                    {{ $project->start_date->format('d M Y') }} &ndash; {{ $project->end_date->format('d M Y') }}
                    &middot; <span class="tb-status" id="tb-status-{{ $project->status === \App\Enums\ProjectStatus::Completed ? 1 : 4 }}">{{ $project->status->label() }}</span>
                </p>
            </div>
            <div>
                <a href="{{ route('projects.index') }}" id="general-btn" class="btn2">
                    <i class="ri-arrow-left-line"></i>Back to Project
                </a>
                <a href="javascript:void(0);" id="general-btn" class="btn1" data-inline-form="#add-task">
                    <i class="ri-add-fill"></i>Add Task
                </a>
            </div>
        </div>

        @if ($taskCount > 0)
            <div id="table-padding" class="project-progress">
                <div class="project-progress-bar">
                    <span style="width: {{ round($done / $taskCount * 100) }}%"></span>
                </div>
                <span class="project-progress-label">{{ $done }} of {{ $taskCount }} done</span>
            </div>
        @endif

        {{-- Inline rather than a dialog, so the board stays visible while you
             type into it. --}}
        <form class="js-inline-form kpi-inline-form" id="add-task" method="POST"
              action="{{ route('project-tasks.store') }}" enctype="multipart/form-data" hidden>
            @csrf
            <input type="hidden" name="project_id" value="{{ $project->id }}">
            @include('projects._task-fields', ['task' => null, 'parentId' => null])
            <div class="kpi-inline-actions">
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Task</button>
                <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
            </div>
        </form>
    </div>

    @forelse ($statuses as $status)
        @php
            $tasks = $tasksByStatus->get($status->value, collect());
            $groupId = 'status-'.$status->value;
        @endphp

        {{-- Every status gets a group, including the empty ones: a board with
             no "Blocked" reads as though nothing can be blocked. --}}
        <div id="tb-box" class="general-box mb-3 kpi-group">
            <div id="table-padding">
                <div class="kpi-group-head">
                    <p id="tb-title" class="mb-0">
                        <span class="tb-status" id="tb-status-{{ $status->colourId() }}">{{ $status->label() }}</span>
                        <span class="kpi-count">{{ $tasks->count() }}</span>
                    </p>
                    <span>
                        <button type="button" class="tb-ac-btn tb-ac-toggle" title="Show"
                                aria-expanded="false" aria-controls="{{ $groupId }}"
                                data-collapse="#{{ $groupId }}">
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                    </span>
                </div>

                {{-- A group with work in it opens by default: a board exists
                     to show the work, and making someone expand every column
                     first defeats it. An empty one stays shut so five "Nothing
                     here" cards do not push the real work off the screen - its
                     heading is still there, so nobody wonders where Blocked
                     went. Either way the reader's own choice wins. --}}
                <div class="js-collapse" id="{{ $groupId }}"
                     data-collapse-default="{{ $tasks->isNotEmpty() ? 'open' : 'closed' }}" hidden>
                    @forelse ($tasks as $task)
                        @include('projects._task', ['task' => $task])
                    @empty
                        <p class="kpi-empty mb-0">Nothing here.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @empty
        <div id="tb-box" class="general-box mb-3">
            <div id="table-padding">
                <p class="kpi-empty mb-0">Nothing here yet. Add the first task above.</p>
            </div>
        </div>
    @endforelse

    @include('projects._quick-create-tag')
@endsection
