@extends('layouts.app')

@section('title', 'Project')

@section('content')
    {!! show_datatable_filter('ProjectList') !!}

    <x-add-button modal="addProjectModal">Add Project</x-add-button>

    {!! show_datatables('ProjectList') !!}

    {{-- A project is four fields, and the work itself is added afterwards on
         its own page - so creating one is an interruption to the list rather
         than a screen of its own.

         Each dialog on this page reopens only for its own fields' errors: a
         failed cancel must not pop Add Project open instead. --}}
    <x-modal id="addProjectModal" title="Add Project" :action="route('projects.store')"
             confirm="Add Project"
             :open-on-error="$errors->hasAny(['title', 'start_date', 'end_date'])">
        @include('projects._form')
    </x-modal>

    {{--
        Cancelling asks why. The reason is required and kept on the project with
        who cancelled it and when, because "why was this stopped?" is the only
        question a cancelled project is ever asked.

        The form's action is filled in per row by public/js/project/modals.js.
        After a failed submit the page reloads, so the action and the project's
        name are restored from the old input instead.
    --}}
    @php
        $cancelId = old('cancel_project_id');
        $cancelTitle = old('cancel_project_title');
    @endphp
    <x-modal id="cancelProjectModal" title="Cancel Project"
             :action="$cancelId ? route('projects.cancel', $cancelId) : '#'"
             :url-template="route('projects.cancel', '__id__')"
             confirm="Cancel Project"
             dismiss="Keep Project"
             :open-on-error="$errors->has('cancel_reason')">
        <input type="hidden" name="cancel_project_id" value="{{ $cancelId }}">
        <input type="hidden" name="cancel_project_title" value="{{ $cancelTitle }}">

        <p id="footer-p" class="mb-3">
            You are cancelling <strong class="js-cancel-project-title">{{ $cancelTitle }}</strong>.
            It will stop taking new tasks. Existing tasks are kept.
        </p>

        <div class="input-group">
            <label>Reason for cancelling<span>*</span></label>
            <textarea class="form-control" name="cancel_reason" rows="4" maxlength="1000" required
                      placeholder="e.g. The client withdrew the requirement.">{{ old('cancel_reason') }}</textarea>
        </div>
    </x-modal>
@endsection
