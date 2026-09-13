<?php

namespace App\Http\Controllers\Kpi;

use App\Http\Controllers\Controller;
use App\Models\ProjectTag;
use App\Models\StaffPosition;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * The project tags a position uses, managed from that position's KPI Setting
 * page - next to the project marks they add up to.
 *
 * Same data as Settings > Project Tag Setting (project_tag.position_ids); this
 * is only a way in from the position's side.
 */
class PositionTagController extends Controller
{
    /**
     * Either brings an existing tag to this position, or creates a new tag for
     * this position only.
     */
    public function store(Request $request, StaffPosition $position): RedirectResponse
    {
        abort_if($position->isAdministrator(), 404);

        if ($request->filled('tag_id')) {
            $tag = ProjectTag::findOrFail($request->integer('tag_id'));

            // A tag already open to every position needs nothing adding.
            if ($tag->positionIds() !== [] && ! in_array($position->id, $tag->positionIds(), true)) {
                $ids = array_merge($tag->positionIds(), [$position->id]);
                sort($ids);
                $tag->update(['position_ids' => $ids]);
            }

            return back()->with('status', 'Tag "'.$tag->name.'" is now available to '.$position->position_name.'.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('project_tag', 'name')->whereNull('deleted_at')],
            'points' => ['required', 'numeric', 'min:0', 'max:9999'],
        ], [
            'name.required' => 'Give the tag a name, or pick an existing tag.',
            'name.unique' => 'A tag with this name already exists - pick it from the list instead.',
        ]);

        $tag = ProjectTag::create($validated + [
            'position_ids' => [$position->id],
            'sort_order' => (int) ProjectTag::max('sort_order') + 1,
        ]);

        return back()->with('status', 'Tag "'.$tag->name.'" added for '.$position->position_name.'.');
    }

    /**
     * Takes this position off a tag. The tag itself stays for its other
     * positions.
     *
     * Refused when this is the tag's only position: an empty list means "every
     * position", so removing the last one would open the tag to everyone -
     * the opposite of what was asked. Delete it in Project Tag Setting instead.
     */
    public function destroy(StaffPosition $position, ProjectTag $tag): RedirectResponse
    {
        $ids = $tag->positionIds();

        if (! in_array($position->id, $ids, true)) {
            return back()->withErrors(['tag' => 'Tag "'.$tag->name.'" is not limited to '.$position->position_name.'.']);
        }

        if (count($ids) === 1) {
            return back()->withErrors(['tag' => 'Tag "'.$tag->name.'" is only for '.$position->position_name
                .'. Removing it here would open it to every position - delete it in Settings > Project Tag Setting instead.']);
        }

        $tag->update(['position_ids' => array_values(array_diff($ids, [$position->id]))]);

        return back()->with('status', 'Tag "'.$tag->name.'" removed from '.$position->position_name.'.');
    }
}
