@extends('layouts.app')

@section('title', 'Edit Member')

@section('content')
    <div id="form-box" class="general-box">
        <form action="{{ route('staff.update', $staff) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <p id="form-sub-title">Edit Member</p>
            @include('staff._form')

            <div id="form-btn-div">
                <a href="{{ route('staff.index') }}" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save Changes</button>
            </div>
        </form>
    </div>

    @include('staff._quick-create-team')
@endsection
