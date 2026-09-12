@extends('layouts.app')

@section('title', 'Project')

@section('content')
    {!! show_datatable_filter('ProjectList') !!}

    <x-add-button modal="addProjectModal">Add Project</x-add-button>

    {!! show_datatables('ProjectList') !!}

    {{-- A project is four fields, and the work itself is added afterwards on
         its own page - so creating one is an interruption to the list rather
         than a screen of its own. --}}
    <x-modal id="addProjectModal" title="Add Project" :action="route('projects.store')"
             confirm="Add Project" open-on-error>
        @include('projects._form')
    </x-modal>
@endsection
