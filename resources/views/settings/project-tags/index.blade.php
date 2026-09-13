@extends('layouts.app')

@section('title', 'Project Tag Setting')

@section('content')
    <div id="tb-box" class="general-box mb-3">
        <div id="table-padding">
            <p id="tb-title" class="mb-1">Project Tag Setting</p>
            <p id="footer-p" class="mb-0">
                Points decide how much each kind of work is worth when a project
                section is scored. Give a tag a position to offer it only on that
                position's tasks; leave it on All positions for everyone. Add, edit
                and remove them inline.
            </p>
        </div>
    </div>

    {{-- Add/edit happens inline in the table - see inlineFields() on ProjectTagList. --}}
    {!! show_datatables('ProjectTagList') !!}
@endsection
