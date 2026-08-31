@extends('layouts.app')

@section('title', 'Position')

@section('content')
    <div id="add-btn-div">
        <a href="javascript:void(0);" id="general-btn" class="btn1" data-bs-toggle="modal" data-bs-target="#addPositionModal">
            <i class="ri-add-fill"></i>Add Position
        </a>
    </div>

    {!! show_datatables('PositionList') !!}

    <div class="modal fade" id="addPositionModal" tabindex="-1">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form action="{{ route('positions.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <p id="modal-title">Add Position</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Position Name<span>*</span></label>
                            <input type="text" class="form-control" name="position_name" required>
                        </div>
                        <div class="input-group">
                            <label>Job Scope</label>
                            <textarea class="form-control" name="job_scope" rows="3"></textarea>
                        </div>
                    </div>
                    <div id="modal-btn-div">
                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal"><i class="ri-close-fill"></i>Cancel</a>
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Confirm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editPositionModal" tabindex="-1" data-url-template="{{ route('positions.update', '__id__') }}">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <p id="modal-title">Edit Position</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Position Name<span>*</span></label>
                            <input type="text" class="form-control" name="position_name" required>
                        </div>
                        <div class="input-group">
                            <label>Job Scope</label>
                            <textarea class="form-control" name="job_scope" rows="3"></textarea>
                        </div>
                    </div>
                    <div id="modal-btn-div">
                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal"><i class="ri-close-fill"></i>Cancel</a>
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
