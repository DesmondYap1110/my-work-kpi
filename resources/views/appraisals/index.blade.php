@extends('layouts.app')

@section('title', 'Appraisal')

@section('content')
    {!! show_datatable_filter('AppraisalList') !!}

    <x-add-button modal="addAppraisalModal">New Appraisal</x-add-button>

    {!! show_datatables('AppraisalList') !!}

    {{-- Opening an appraisal is picking a member and a period; the form itself
         is filled in on its own page afterwards, so this is an interruption to
         the list rather than a screen of its own. --}}
    @include('appraisals._new-appraisal')
@endsection
