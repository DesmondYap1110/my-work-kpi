@extends('layouts.app')

@section('title', 'Review Schedule')

@section('content')
    {{--
        Review Schedule: how often each member is appraised and when the next
        one is due. Paged and filtered on the server, so it stays usable with
        many members across many teams - see ReviewScheduleList.

        Next due = the last generated appraisal's Next Assessment date, else its
        period end plus the cycle, else the joined date plus the cycle. See
        App\Services\AppraisalScheduleService.
    --}}
    <div id="tb-box" class="general-box mb-3">
        <div id="table-padding" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <p id="tb-title" class="mb-0 d-flex align-items-center gap-2">
                Review Schedule
                <button type="button" class="kpi-help-btn" aria-label="How does the review schedule work?"
                        data-help-hover="Pick how often each member is appraised - it saves straight away. The next appraisal is due one cycle after the last generated appraisal ends, or on its Next Assessment date if one was set. Manual means no schedule. Filter by team or status to find who needs reviewing.">
                    <i class="ri-question-line"></i>
                </button>
                @if ($overdueCount)
                    <a href="{{ route('appraisals.schedule', ['state' => 'overdue']) }}" class="tb-status" id="tb-status-2">{{ $overdueCount }} overdue</a>
                @endif
                @if ($soonCount)
                    <a href="{{ route('appraisals.schedule', ['state' => 'soon']) }}" class="tb-status" id="tb-status-3">{{ $soonCount }} due soon</a>
                @endif
            </p>

            {{-- The lead time for "due soon": the dashboard notice and the
                 Status filter both follow it. kpi_setting.appraisal_notice_days. --}}
            <form action="{{ route('appraisals.notice.update') }}" method="POST" class="appraisal-notice-form">
                @csrf @method('PUT')
                <label for="appraisal-notice-days" class="mb-0">Notify me</label>
                <input type="number" id="appraisal-notice-days" name="appraisal_notice_days" class="form-control"
                       min="0" max="60" required value="{{ old('appraisal_notice_days', $noticeDays) }}">
                <span>days before due</span>
                <button type="submit" class="appraisal-notice-save" title="Save" aria-label="Save notice days"><i class="ri-save-3-line"></i></button>
                <button type="button" class="kpi-help-btn" aria-label="What does the notice period do?"
                        data-help-hover="A member shows as due soon - on the dashboard and here - this many days before their next appraisal. 0 means only on the day it is due. Overdue members always show.">
                    <i class="ri-question-line"></i>
                </button>
            </form>
        </div>
        @error('appraisal_notice_days')
            <p class="text-danger mb-0 px-3 pb-2">{{ $message }}</p>
        @enderror
    </div>

    {!! show_datatable_filter('ReviewScheduleList') !!}

    {!! show_datatables('ReviewScheduleList') !!}

    {{-- The Start button on a row opens this with the member chosen. --}}
    @include('appraisals._new-appraisal')
@endsection
