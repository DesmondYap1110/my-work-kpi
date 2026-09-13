@extends('layouts.app')

@section('title', 'Appraisal')

@section('content')
    {!! show_datatable_filter('AppraisalList') !!}

    {{-- Export takes the filters above with it - see export-link.js. Print
         prints the page of the list on screen. --}}
    <div id="add-btn-div" class="d-flex justify-content-end flex-wrap gap-2">
        <a href="{{ route('appraisals.export') }}" id="general-btn" class="btn2" download data-export-filters="AppraisalList">
            <i class="ri-file-download-line"></i>Export CSV
        </a>
        <button type="button" id="general-btn" class="btn2" onclick="window.print()">
            <i class="ri-printer-line"></i>Print
        </button>
        <x-button variant="primary" icon="ri-add-fill" data-bs-toggle="modal" data-bs-target="#addAppraisalModal">New Appraisal</x-button>
    </div>

    {!! show_datatables('AppraisalList') !!}

    {{-- Opening an appraisal is picking a member and a period; the form itself
         is filled in on its own page afterwards, so this is an interruption to
         the list rather than a screen of its own. --}}
    @include('appraisals._new-appraisal')
@endsection
