{{--
    The theme's modal shell. Wraps a form when an action is given, which is
    what every modal in this app does.

    <x-modal id="addTeamModal" title="Add Team" :action="route('teams.store')">
        <x-form.input name="team_name" label="Team Name" required />
    </x-modal>

    @param string      $id
    @param string      $title
    @param string|null $action   form action; omit for a content-only modal
    @param string      $method   POST|PUT|PATCH|DELETE (spoofed as needed)
    @param string|null $urlTemplate  data-url-template for JS-populated edit
                                     modals (see public/js/project/modals.js)
    @param string      $confirm  label for the confirm button
--}}
@props([
    'id',
    'title',
    'action' => null,
    'method' => 'POST',
    'urlTemplate' => null,
    'confirm' => 'Confirm',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1"
     @if ($urlTemplate) data-url-template="{{ $urlTemplate }}" @endif>
    <div class="modal-dialog" id="md-dialog">
        <div class="modal-content general-box" id="md-content">
            <form @if ($action) action="{{ $action }}" @endif method="POST">
                @csrf
                @if (! in_array(strtoupper($method), ['GET', 'POST']))
                    @method($method)
                @endif

                <div class="modal-header">
                    <p id="modal-title">{{ $title }}</p>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div id="modal-div">
                    {{ $slot }}
                </div>

                <div id="modal-btn-div">
                    <x-button variant="secondary" icon="ri-close-fill" dismiss>Cancel</x-button>
                    <x-button variant="primary" icon="ri-check-fill">{{ $confirm }}</x-button>
                </div>
            </form>
        </div>
    </div>
</div>
