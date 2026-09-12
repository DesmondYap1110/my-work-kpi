{{--
    Create a team without leaving the member form.

    Must sit OUTSIDE the member <form> - a nested form is invalid HTML and the
    browser drops it. The submit is intercepted and posted on its own, and the
    new team is added to the Team dropdown; see
    public/js/modules/quick-create.js.
--}}
<x-modal id="quickCreateTeam" title="Add Team" :action="route('teams.store')" confirm="Add Team"
         data-quick-create-target="[name=team_id]">
    <x-form.input name="team_name" label="Team Name" required maxlength="255"
                  placeholder="e.g. Management, Engineering" />
</x-modal>
