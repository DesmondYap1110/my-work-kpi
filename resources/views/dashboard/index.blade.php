@extends('layouts.app')

@section('title', 'Dashboard')
@section('section-id', 'ft-section')

@section('content')
    {{-- The administrator's tiles count the company and link into pages only
         an administrator may open; everyone else sees their own standing.
         See DashboardController. --}}
    @include($isAdmin ? 'dashboard._admin' : 'dashboard._staff')
@endsection
