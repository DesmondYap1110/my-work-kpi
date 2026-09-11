@extends('layouts.app')

@section('title', 'Team')

@section('content')
    {{-- Add/edit happens inline in the table - see inlineFields() on TeamList. --}}
    {!! show_datatables('TeamList') !!}
@endsection
