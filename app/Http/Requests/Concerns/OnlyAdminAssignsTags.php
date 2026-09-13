<?php

namespace App\Http\Requests\Concerns;

/**
 * Drops tag_id from a task request that did not come from the administrator.
 *
 * A tag carries points, and points are what a delivery score is made of - so
 * anyone who can tag their own task can set their own KPI. Members may plan
 * and run projects; pricing the work is the administrator's.
 *
 * Removed before validation rather than refused, so an ordinary edit is not
 * blocked by a field the form did not even show. On a create the task is saved
 * untagged; on an edit the field is simply absent from the update, so whatever
 * tag the administrator set stays as it was.
 *
 * One trait rather than a copy in each request: this is the rule that stops
 * people scoring themselves, and it should not be possible to fix it in one
 * place and forget the other.
 */
trait OnlyAdminAssignsTags
{
    protected function prepareForValidation(): void
    {
        // The request's own user rather than the Auth facade: it is the same
        // person on a real request, and it means this rule can be tested
        // without standing up a session.
        if (! $this->user()?->isAdmin()) {
            $this->request->remove('tag_id');
        }
    }
}
