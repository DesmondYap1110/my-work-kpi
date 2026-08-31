@extends('layouts.app')

@section('title', 'KPI Objectives - '.$kpi->kpi_title)

@section('content')
    <div id="tb-box" class="general-box mb-3">
        <div id="table-padding">
            <p id="tb-title" class="mb-1">{{ $kpi->kpi_title }}</p>
            <p id="footer-p" class="mb-0">{{ $kpi->position->position_name ?? '-' }}</p>
        </div>
    </div>

    <div id="add-btn-div">
        <a href="{{ route('kpi.index') }}" id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back to KPI List</a>
        <a href="javascript:void(0);" id="general-btn" class="btn1" data-bs-toggle="modal" data-bs-target="#addObjectiveModal"><i class="ri-add-fill"></i>Add Objective</a>
    </div>

    {!! show_datatables('KpiObjectiveList', ['kid' => $kpi->kpi_id]) !!}

    @php
        $markOptions = [
            'objmk_2' => '+2pts',
            'objmk_1' => '+1pts',
            'objmk_0' => '0pts',
            'objmk_n1' => '-1pts',
            'objmk_n2' => '-2pts',
        ];
    @endphp

    <div class="modal fade" id="addObjectiveModal" tabindex="-1">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form action="{{ route('kpi.objectives.store', $kpi->kpi_id) }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <p id="modal-title">Add Objective</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Objective<span>*</span></label>
                            <select class="form-control" name="kojbInfo_id" required>
                                <option value="" selected disabled>Select Objective</option>
                                @foreach ($objectiveCatalog as $info)
                                    <option value="{{ $info->kojbInfo_id }}">{{ $info->kojbInfo_title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Type<span>*</span></label>
                            <select class="form-control" name="obj_type" required>
                                <option value="" selected disabled>Select Type</option>
                                @foreach (\App\Enums\ObjectiveType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Mark Range<span>*</span></label>
                        </div>
                        @foreach ($markOptions as $field => $label)
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="add_{{ $field }}" name="{{ $field }}" value="1" checked>
                                <label class="form-check-label" for="add_{{ $field }}">{{ $label }}</label>
                            </div>
                        @endforeach
                    </div>
                    <div id="modal-btn-div">
                        <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal"><i class="ri-close-fill"></i>Cancel</a>
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Objective</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="editObjectiveModal" tabindex="-1" data-url-template="{{ route('kpi.objectives.update', [$kpi->kpi_id, '__id__']) }}">
        <div class="modal-dialog" id="md-dialog">
            <div class="modal-content general-box" id="md-content">
                <form method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header">
                        <p id="modal-title">Edit Objective</p>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div id="modal-div">
                        <div class="input-group">
                            <label>Type<span>*</span></label>
                            <select class="form-control" name="obj_type" required>
                                <option value="" disabled>Select Type</option>
                                @foreach (\App\Enums\ObjectiveType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Mark Range<span>*</span></label>
                        </div>
                        @foreach ($markOptions as $field => $label)
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="edit_{{ $field }}" name="{{ $field }}" value="1">
                                <label class="form-check-label" for="edit_{{ $field }}">{{ $label }}</label>
                            </div>
                        @endforeach
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
