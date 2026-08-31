@extends('layouts.app')

@section('title', 'Manage Project')

@section('content')
    {!! show_datatable_filter('ProjectList') !!}

    <div id="add-btn-div">
        <a href="{{ route('projects.create') }}" id="general-btn" class="btn1"><i class="ri-add-fill"></i>Add Project</a>
    </div>

    {!! show_datatables('ProjectList') !!}
@endsection
