@extends('layouts.app')

@section('title', 'Add Member')

@section('content')
    <div id="form-box" class="general-box">
        <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <p id="form-sub-title">Add Member</p>
            @include('staff._form')

            <div id="form-btn-div">
                <a href="{{ route('staff.index') }}" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Member</button>
            </div>
        </form>
    </div>
@endsection
