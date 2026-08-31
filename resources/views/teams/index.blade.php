@extends('layouts.app')

@section('title', 'Team')

@section('content')
    <x-add-button modal="addTeamModal">Add Team</x-add-button>

    {!! show_datatables('TeamList') !!}

    <x-modal id="addTeamModal" title="Add Team" :action="route('teams.store')">
        <x-form.input name="team_name" label="Team Name" required />
    </x-modal>

    <x-modal id="editTeamModal" title="Edit Team" method="PUT" confirm="Save Changes"
             :url-template="route('teams.update', '__id__')">
        <x-form.input name="team_name" label="Team Name" required />
    </x-modal>
@endsection
