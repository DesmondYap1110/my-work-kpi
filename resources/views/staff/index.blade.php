@extends('layouts.app')

@section('title', 'Member')

@section('content')
    {!! show_datatable_filter('StaffList') !!}

    <div id="add-btn-div">
        <a href="{{ route('staff.create') }}" id="general-btn" class="btn1"><i class="ri-add-fill"></i>Add Member</a>
    </div>

    {!! show_datatables('StaffList') !!}
@endsection
