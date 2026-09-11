@extends('layouts.app')

@section('title', 'Position')

@section('content')
    {{-- Add/edit happens inline in the table - see inlineFields() on PositionList. --}}
    {!! show_datatables('PositionList') !!}
@endsection
