@extends('layouts.app')

@section('title', 'Team')

@section('content')
    <div id="add-btn-div">
        <a href="javascript:void(0);" id="general-btn" class="btn1" data-bs-toggle="modal" data-bs-target="#addTeamModal">
            <i class="ri-add-fill"></i>Add Team
        </a>
    </div>

    {!! show_datatables('TeamList') !!}

    <div class="modal fade" id="addTeamModal" tabindex="-1">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form action="{{ route('teams.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <p id="modal-title">Add Team</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Team Name<span>*</span></label>
                            <input type="text" class="form-control" name="team_name" required>
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

    <div class="modal fade" id="editTeamModal" tabindex="-1" data-url-template="{{ route('teams.update', '__id__') }}">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <p id="modal-title">Edit Team</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Team Name<span>*</span></label>
                            <input type="text" class="form-control" name="team_name" required>
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
