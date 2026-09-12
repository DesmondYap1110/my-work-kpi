{{--
    Create a tag without leaving the task you are writing.

    Must sit OUTSIDE any task form - a nested form is invalid HTML and the
    browser drops it. The submit is intercepted and posted on its own, and the
    new tag is added to every tag dropdown on the page; see
    public/js/modules/quick-create.js.
--}}
<x-modal id="quickCreateTag" title="Add Tag" :action="route('project-tags.store')" confirm="Add Tag"
         data-quick-create-target="select[name=tag_id]">
    <x-form.input name="name" label="Tag Name" required maxlength="255"
                  placeholder="e.g. Major feature, Bug fix" />
    <x-form.input name="points" label="Points" type="number" required
                  step="0.01" min="0" max="9999" placeholder="e.g. 10" />
</x-modal>
