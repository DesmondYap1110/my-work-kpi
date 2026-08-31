@extends('layouts.app')

@section('title', 'View Member KPI')

@push('styles')
    <style type="text/css">
        /*BACKGROUND SECTION*/
        #bg-section {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        #bg-box {
            border-radius: 5px;
            padding: 25px 15px;
        }
        #bg-left-div {
            display: flex;
            align-items: center;
        }
        #bg-img-div {
            margin-right: 15px;
        }
        #bg-img-div img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
        }
        #bg-name {
            font-size: 24px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 3px;
            color: #1896BD;
        }
        #bg-info-p {
            display: flex;
            color: #888;
            align-items: center;
            font-size: 13px;
            line-height: 1.4;
            margin-bottom: 3px;
        }
        #bg-info-p:last-child {
            margin-bottom: 0;
        }
        #bg-info-p i {
            margin-right: 3px;
        }
        #bg-since {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 0;
        }
        #bg-btn-div {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            justify-content: end;
        }
        #bg-btn-div a:last-child {
            margin-right: 0;
        }
        #bg-btn-div a {
            margin-right: 15px;
        }
        #bg-ft-div {
            width: 100%;
            align-items: center;
            display: inline-flex;
            justify-content: flex-end;
        }
        #bg-ft-box {
            display: flex;
            overflow: hidden;
            position: relative;
            align-items: center;
            padding: 0 0 15px 40px;
        }
        #bg-ft-title {
            font-size: 13px;
            font-weight: 500;
            line-height: 1.4;
            margin-bottom: 0;
            color: #000000;
        }
        #bg-ft-p {
            font-size: 21px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0;
            color: #1896BD;
        }
        #bg-ft-icon-div {
            margin-right: 10px;
            
        }
        #bg-ft-icon {
            color:#fff;
            width: 50px;
            height: 50px;
            font-size: 20px;
            line-height: 50px;
            text-align: center;
            border-radius: 50%;
            box-shadow: 0 5px 10px #4be8d340;
            background: linear-gradient(to bottom right, #4be8d4 0%, #129bd2 100%);
        }
        .profile-nav.nav-pills .nav-link {
            border-radius: unset;
            padding: 0 15px 15px 15px;
        }
        #att-div {
            overflow: auto;
            height: 150px;
        }
        #att-box {
            margin-bottom: 30px;
        }
        #att-box:last-child {
            margin-bottom: 0;
        }
        #att-name {
            font-size: 15px;
            font-weight: 600;
            line-height: 1.4;
            margin-bottom: 0;
        }
        #att-time {
            font-size: 11px;
            line-height: 1.4;
            margin-bottom: 5px;
        }
        #att-p {
            font-size: 12px;
            line-height: 1.4;
            margin-bottom: 0;
        }
        .dropdown .dropdown-toggle {
            padding-right: 35px !important;
        }
        #form-div:last-child {
            margin-bottom: 0;
        }
        #tb-sub-til
        {
            background: #01216140 !important;
            color: #000;
            font-weight: 600;
            font-size: 13px;
        }
        @media (max-width: 991px){
            #bg-left-div {
                display: block;
                align-items: unset;
                text-align: center;
            }
            #bg-img-div {
                margin-right: 0;
                margin-bottom: 15px;
            }
            #bg-img-div img {
                width: 125px;
                height: 125px;
            }
            #bg-rank-icon {
                width: 45px;
            }
            #bg-name {
                font-size: 18px;
                margin-bottom: 2px;
            }
            #bg-position-div span {
                font-size: 11px;
            }
            #bg-since {
                font-size: 9px;
            }
            #bg-ft-div {
                width: unset;
                margin-top: 30px;
                align-items: unset;
                display: -webkit-box;
                justify-content: unset;
            }
            #bg-ft-box {
                display: block;
                padding: 0 15px;
                text-align: center;
            }
            #bg-ft-icon-div {
                margin-right: 0;
                margin-bottom: 15px;
            }
            #bg-ft-icon {
                margin: 0 auto;
            }
            #bg-ft-title {
                font-size: 11px;
                margin-bottom: 2px;
            }
            #bg-ft-p {
                font-size: 18px;
            }
            #bg-ul {
                justify-content: space-between;
            }
            #bg-btn-div {
                margin-bottom: 0;
                margin-top: 15px;
                justify-content: center;
            }
            #att-name {
                font-size: 13px;
            }
            #att-time {
                font-size: 9px;
            }
            #att-p {
                font-size: 10px;
            }
        }

        /*FORM SECTION*/
        .form-check .form-check-input {
            float: none;
        }
        .form-check {
            text-align: right;
        }
        .form-check-input {
            width: 45px !important;
            height: 20px;
            margin: 0;
        }
        @media (max-width: 991px){
            .form-check-input {
                width: 35px !important;
            }
        }

    </style>
@endpush

@section('content')
    <section id="bg-section">
        <div class="container-fluid">
        <div id="bg-box" class="general-box">
            <div class="row align-items-center">
                <div class="col-lg-5">
                    <div id="bg-left-div">
                        <div id="bg-img-div" class="profile-user position-relative">
                            <img src="{{ $staff->staffimg ? asset('storage/staff-photos/'.$staff->staffimg) : asset('storage/staff-photos/default.jpg') }}" alt="Profile" title="Profile">
                        </div>
                        <div id="bg-info-div">
                            <p id="bg-name">{{ $staff->staff_name }}</p>
                            <p id="bg-info-p">{{ $staff->position->position_name ?? '-' }}</p>
                            <p id="bg-info-p"><i class="ri-team-line"></i>{{ $staff->team->team_name ?? '-' }}</p>
                            <p id="bg-info-p"><i class="ri-calendar-fill"></i>{{ optional($staff->datejoincompany)->format('d M Y') }}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div id="bg-btn-div" class="dropdown">
                        <a href="{{ route('staff.index') }}" id="general-btn" class="btn2"><i class="ri-arrow-left-line"></i>Back</a>
                        <a href="{{ route('staff.edit', $staff->staff_id) }}" id="general-btn" class="btn1"><i class="ri-edit-2-line"></i>Edit Member</a>
                    </div>
                    <div id="bg-ft-div">
                        <div id="bg-ft-box">
                            <div id="bg-ft-icon-div">
                                <div id="bg-ft-icon" class="bg-ft-icon-1">
                                    <i class="ri-percent-line"></i>
                                </div>
                            </div>
                            <div>
                                <p id="bg-ft-title">Total Score</p>
                                <p id="bg-ft-p">{{ $overallScore['percentage'] }} %</p>
                            </div>
                        </div>
                        <div id="bg-ft-box">
                            <div id="bg-ft-icon-div">
                                <div id="bg-ft-icon" class="bg-ft-icon-3">
                                    <i class="ri-bar-chart-line"></i>
                                </div>
                            </div>
                            <div>
                                <p id="bg-ft-title">Total KPI Point</p>
                                <p id="bg-ft-p">{{ $overallScore['total_mark'] }}/{{ $overallScore['max_possible'] }}</p>
                            </div>
                        </div>
                        <div id="bg-ft-box">
                            <div id="bg-ft-icon-div">
                                <div id="bg-ft-icon" class="bg-ft-icon-3">
                                    <i class="ri-clipboard-line"></i>
                                </div>
                            </div>
                            <div>
                                <p id="bg-ft-title">Total Project</p>
                                <p id="bg-ft-p">{{ $completedProjects->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    <div class="row">
        <div class="col-lg-6">
            <div id="form-box" class="general-box">
                <form name="profile_form" method="POST" action="">
                        <div id="form-div">
                            <p id="form-sub-title">Personal Information</p>
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>IC No</label>
                                        <input type="text" class="form-control" value="{{ $staff->ic }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>Gender</label>
                                        <input type="text" class="form-control" value="{{ $staff->gender }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>Birth Date</label>
                                        <input type="date" class="form-control" value="{{ optional($staff->dob)->format('Y-m-d') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>Join Team Date</label>
                                        <input type="date" class="form-control" value="{{ optional($staff->datejointeam)->format('Y-m-d') }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>Contact No.</label>
                                        <input type="tel" class="form-control" value="{{ $staff->contact }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>Email Address</label>
                                        <input type="email" class="form-control" value="{{ $staff->email }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div id="form-div">
                            <p id="form-sub-title">Address Information</p>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="input-group">
                                        <label>Address Details</label>
                                        <input type="text" class="form-control" value="{{ $staff->staff_address }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>Postcode</label>
                                        <input type="text" class="form-control" value="{{ $staff->postcode }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>City</label>
                                        <input type="text" class="form-control" value="{{ $staff->city }}" readonly>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="input-group">
                                        <label>State</label>
                                        <input type="text" class="form-control" value="{{ $staff->states }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <div id="form-box" class="general-box">
                    <p id="form-sub-title">KPI Record</p>
                    <div class="row">
                        <div class="col-lg-12">
                            <div style="margin-bottom: 15px;">
                                <form name="filter_form" method="GET" action="">
                                    <div class="row align-items-center">
                                        <div class="col-lg-6">
                                            <div class="input-group">
                                                <label>Project</label>
                                                <select class="form-control" name="pid">
                                                    <option value="" disabled @selected(! $selectedProjectId)>Select Project</option>
                                                    @foreach ($completedProjects as $project)
                                                        <option value="{{ $project->project_id }}" @selected($selectedProjectId == $project->project_id)>{{ $project->p_Title }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-6">
                                            <div id="filter-btn-div">
                                                <button type="submit" id="general-btn" class="btn1"><i class="ri-filter-2-line"></i>Filter</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div id="form-div">
                                <div id="table-div">
                                    <table class="table table-bordered dt-responsive nowrap align-middle">
                                        <thead>
                                            <tr>
                                                <th>Objective</th>
                                                <th class="text-center">Marks</th>
                                                <th class="text-center">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" id="tb-sub-til">{{ $staff->position->job_scope ?? '-' }}</td>
                                            </tr>
                                            @foreach ($standardEntries as $entry)
                                                <tr>
                                                    <td>{{ $entry->objectiveInfo->kojbInfo_title ?? '-' }}</td>
                                                    <td class="text-center">{{ $entry->mark }}</td>
                                                    <td class="text-center">
                                                        @if (is_null($entry->createddate) && is_null($entry->status))
                                                            -
                                                        @elseif (is_null($entry->status))
                                                            <span class="tb-status" id="tb-status-3">Pending</span>
                                                        @elseif ($entry->status === \App\Enums\ProjectKpiStatus::Approved)
                                                            <span class="tb-status" id="tb-status-1">Approved</span>
                                                        @else
                                                            <span class="tb-status" id="tb-status-2">Reject</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr>
                                                <td colspan="3" id="tb-sub-til">Extra Point</td>
                                            </tr>
                                            @foreach ($extraEntries as $entry)
                                                <tr>
                                                    <td>{{ $entry->objectiveInfo->kojbInfo_title ?? '-' }}</td>
                                                    <td class="text-center">{{ $entry->mark }}</td>
                                                    <td class="text-center">
                                                        @if (is_null($entry->createddate) && is_null($entry->status))
                                                            -
                                                        @elseif (is_null($entry->status))
                                                            <span class="tb-status" id="tb-status-3">Pending</span>
                                                        @elseif ($entry->status === \App\Enums\ProjectKpiStatus::Approved)
                                                            <span class="tb-status" id="tb-status-1">Approved</span>
                                                        @else
                                                            <span class="tb-status" id="tb-status-2">Reject</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div id="form-div">
                                <div id="table-div">
                                    <table class="table table-bordered dt-responsive nowrap align-middle">
                                        <thead>
                                            <tr>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Self-Add</th>
                                                <th class="text-center">KPI Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @if ($projectScore)
                                                    <td class="text-center">{{ $projectScore['total_mark'] }}/{{ $projectScore['max_possible'] }}</td>
                                                    <td class="text-center">{{ $projectScore['total_mark'] }}</td>
                                                    <td class="text-center">{{ $projectScore['percentage'] }} %</td>
                                                @else
                                                    <td class="text-center">-</td>
                                                    <td class="text-center">-</td>
                                                    <td class="text-center">-</td>
                                                @endif
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection
