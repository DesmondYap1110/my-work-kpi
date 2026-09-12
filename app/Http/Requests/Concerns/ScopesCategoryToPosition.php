<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Categories belong to a position, so a posted category_id is only valid if it
 * is one of the categories of the position named in the route.
 */
trait ScopesCategoryToPosition
{
    protected function categoryBelongsToPosition(): Exists
    {
        return Rule::exists('kpi_category', 'id')
            ->where('position_id', $this->route('position')?->id)
            ->whereNull('deleted_at');
    }
}
