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
                How much of a KPI score comes from delivering project work. The rest
                comes from the objectives a member is rated on. A company that runs
                no projects leaves this at 0 and scores on objectives alone.
            </p>

            <div class="row">
                <div class="col-lg-6">
                    <div class="input-group">
                        <label>Project Weighting<span>*</span></label>
                        <div class="weighting-field">
                            <input type="number" class="form-control" name="project_weight"
                                   min="0" max="100" step="1" value="{{ $weight }}" required>
                            <span class="weighting-suffix">% delivery</span>
                        </div>
                        <span id="note-p" class="d-block">
                            Objectives take the remaining {{ 100 - (int) $weight }}%.
                        </span>
                    </div>
                </div>
            </div>

            <p id="form-sub-title" class="mt-4">Per-position override</p>
            <p id="footer-p" class="mb-3">
                Leave blank to follow the company figure. Set a number where that
                answer does not fit the role &mdash; an office admin who is never
                assigned project work belongs at 0.
            </p>

            <div id="table-div">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th class="text-center">Delivery %</th>
                            <th class="text-center">Objectives %</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($positions as $position)
                            @php
                                $override = old('positions.'.$position->id, $position->project_weight);
                            @endphp
                            <tr>
                                <td>{{ $position->position_name }}</td>
                                <td class="text-center">
                                    <input type="number" class="form-control weighting-cell"
                                           name="positions[{{ $position->id }}]"
                                           min="0" max="100" step="1"
                                           value="{{ $override }}"
                                           placeholder="{{ $weight }}">
                                </td>
                                <td class="text-center">
                                    {{ 100 - (int) ($override ?? $weight) }}%
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center">No Record</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div id="form-btn-div">
                <a href="{{ route('projects.index') }}" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save Weighting</button>
            </div>
        </form>
    </div>
@endsection
