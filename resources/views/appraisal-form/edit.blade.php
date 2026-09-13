@extends('layouts.app')

@section('title', 'Project Form Setup')

@section('content')
    <div id="tb-box" class="general-box mb-3">
        <div id="table-padding">
            <p id="tb-title" class="mb-0 d-flex align-items-center gap-2">
                Performance Bands
                <button type="button" class="kpi-help-btn" aria-label="What are performance bands?"
                        data-help-hover="What a final KPI score is called, and what it means for the review - the outcome decides whether a probation is confirmed, extended or ended. Ranges may not overlap. Add, edit and remove them in the table.">
                    <i class="ri-question-line"></i>
                </button>
            </p>
        </div>
    </div>

    {{-- Add/edit happens inline in the table - see PerformanceBandList. --}}
    {!! show_datatables('PerformanceBandList') !!}
@endsection
