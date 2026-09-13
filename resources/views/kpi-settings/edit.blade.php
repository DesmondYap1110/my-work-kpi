@extends('layouts.app')

@section('title', 'KPI Weighting')

@section('content')
    @php
        $weight = old('project_weight', $setting->project_weight);
    @endphp

    <div id="form-box" class="general-box">
        <form action="{{ route('kpi-settings.update') }}" method="POST">
            @csrf @method('PUT')

            <p id="form-sub-title">KPI Weighting</p>
            <p id="footer-p" class="mb-3">
                The company figure for how a KPI score out of 100 is <strong>calculated</strong>:
                how much comes from <strong>project work</strong>, and how much from the KPI
                objectives a member is rated on. A company that runs no projects leaves this
                at 0 and scores on objectives alone.
            </p>

            <div class="row">
                <div class="col-lg-6">
                    <div class="input-group">
                        <label>Points from projects<span>*</span></label>
                        <div class="weighting-field">
                            <input type="number" class="form-control" name="project_weight"
                                   min="0" max="100" step="1" value="{{ $weight }}" required>
                            <span class="weighting-suffix">of 100</span>
                        </div>
                        <span id="note-p" class="d-block kpi-weighting-note">
                            KPI objectives give the remaining {{ 100 - (int) $weight }} points.
                        </span>
                    </div>
                </div>
            </div>

            <p id="form-sub-title" class="mt-4">Per position</p>
            {{-- Set on each position's KPI page, beside the objectives it
                 weighs against - see kpi/objectives/_project-kpi. --}}
            <p id="footer-p" class="mb-3">
                A position can have its own points from projects and its own project marks target.
                Set them on the position's KPI page: <strong>Human Resource &rsaquo; Position</strong>,
                then click <strong>Yes</strong> under KPI Assigned.
            </p>

            @if ($overrides->isNotEmpty())
                <div id="table-div" class="mb-3">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Position with its own figure</th>
                                <th class="text-center">Points from projects</th>
                                <th class="text-center">Project marks target</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($overrides as $position)
                                <tr>
                                    <td>{{ $position->position_name }}</td>
                                    <td class="text-center">{{ $position->project_weight !== null ? $position->project_weight.' / 100' : 'Company figure' }}</td>
                                    <td class="text-center">
                                        {{ $position->project_target !== null ? rtrim(rtrim((string) $position->project_target, '0'), '.').' marks' : '-' }}
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('kpi.objectives.index', $position->id) }}" class="tb-ac-btn" id="tb-ac-btn-4" title="Open this position's KPI">
                                            <i class="ri-external-link-line"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p id="footer-p" class="mb-3">Every position follows the company figure.</p>
            @endif

            <div id="form-btn-div">
                <a href="{{ route('projects.index') }}" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save Weighting</button>
            </div>
        </form>
    </div>
@endsection
