@extends('layouts.app')

{{-- Reached two ways: "Task" (every task, administrator) and "My Tasks"
     (the viewer's own). The rows are narrowed by ProjectTaskListQuery, not
     here - see ProjectTaskController::mine(). --}}
@section('title', $heading ?? 'Task')

@section('content')
    {!! show_datatable_filter('ProjectTaskList') !!}

    {!! show_datatables('ProjectTaskList') !!}

    <div class="modal fade" id="attachmentsModal" tabindex="-1" data-url-template="{{ route('project-tasks.attachments', '__id__') }}">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <div class="modal-header">
                    <p id="modal-title">Attachment History</p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div id="modal-div"></div>
            </div>
        </div>
    </div>
@endsection
