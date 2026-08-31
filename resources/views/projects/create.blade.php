@extends('layouts.app')

@section('title', 'Add Project')

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div id="form-box" class="general-box">
                <form action="{{ route('projects.store') }}" method="POST">
                    @csrf
                    <p id="form-sub-title">Add Project</p>
                    @include('projects._form')
                    <div id="form-btn-div">
                        <a href="{{ route('projects.index') }}" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-fill"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
