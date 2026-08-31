@extends('layouts.app')

@section('title', 'Add Project Phase')

@section('content')
    <div class="row">
        <div class="col-lg-6">
            <div id="form-box" class="general-box">
                <form action="{{ route('project-phases.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="p_ID" value="{{ $project->project_id }}">
                    <p id="form-sub-title">Add Project Phase</p>
                    @include('project-phases._form')
                    <div id="form-btn-div">
                        <a href="{{ route('project-phases.index', ['project_id' => $project->project_id]) }}" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-save-3-fill"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
