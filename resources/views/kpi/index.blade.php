@extends('layouts.app')

@section('title', 'Manage KPI')

@section('content')
    <div id="add-btn-div">
        <a href="javascript:void(0);" id="general-btn" class="btn1 @if($availablePositions->isEmpty()) btn6 @endif" @if($availablePositions->isNotEmpty()) data-bs-toggle="modal" data-bs-target="#addKpiModal" @endif>
            <i class="ri-add-fill"></i>Add KPI
        </a>
    </div>
    @if ($availablePositions->isEmpty())
        <p id="general-note" class="text-end mb-3">Every position already has a KPI template assigned.</p>
    @endif

    {!! show_datatables('KpiList') !!}

    <div class="modal fade" id="addKpiModal" tabindex="-1">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form action="{{ route('kpi.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <p id="modal-title">Add KPI</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Title<span>*</span></label>
                            <input type="text" class="form-control" name="kpi_title" maxlength="200" required>
                        </div>
                        <div class="input-group">
                            <label>Position<span>*</span></label>
                            <select class="form-control" name="position_ID" required>
                                <option value="" selected disabled>Select Position</option>
                                @foreach ($availablePositions as $position)
                                    <option value="{{ $position->position_ID }}">{{ $position->position_name }}</option>
                                @endforeach
                            </select>
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

    <div class="modal fade" id="editKpiModal" tabindex="-1" data-url-template="{{ route('kpi.update', '__id__') }}">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <p id="modal-title">Edit KPI</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Title<span>*</span></label>
                            <input type="text" class="form-control" name="kpi_title" maxlength="200" required>
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
