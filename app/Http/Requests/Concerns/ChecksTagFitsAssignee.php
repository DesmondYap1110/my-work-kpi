<?php

namespace App\Http\Requests\Concerns;

use App\Models\ProjectTag;
use App\Models\Staff;
use Illuminate\Validation\Validator;

/**
 * A tag for certain positions can only go on a task whose assignee holds one
 * of them - "campaign" is not a developer's work.
 *
 * The task form already hides tags that do not fit the chosen assignee (see
 * public/js/modules/tag-position.js); this is the rule behind it. A tag open to
 * every position, and an unassigned task, always pass.
 */
trait ChecksTagFitsAssignee
{
    protected function checkTagFitsAssignee(Validator $validator): void
    {
        // Absent when a member saved the task - OnlyAdminAssignsTags drops it,
        // and the administrator's tag is left as it was.
        if (! $this->filled('tag_id') || ! $this->filled('assignee_id')) {
            return;
        }

        $tag = ProjectTag::find($this->input('tag_id'));
        $assignee = Staff::find($this->input('assignee_id'));

        if (! $tag || ! $assignee || $tag->allowsPosition($assignee->position_id)) {
            return;
        }

        $validator->errors()->add(
            'tag_id',
            'The tag "'.$tag->name.'" is not for '.($assignee->position->position_name ?? 'this member\'s position')
                .'. Pick a tag for that position, or change the assignee.'
        );
    }
}
