<?php

namespace App\Components\Datatables;

use App\Models\StaffPosition;
use App\Queries\ProjectTagListQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Project tags and their point weights, added and edited inline.
 *
 * The points drive the project half of an assessment score, so this is the
 * screen a company tunes to match how it values different kinds of work.
 */
class ProjectTagList extends Datatables
{
    public static function getTableColumns(): array
    {
        return [
            'name' => 'Tag',
            'points' => 'Points',
            'positions' => 'Positions',
            'usage_count' => 'Used By',
            'action' => 'Actions',
        ];
    }

    public function inlineFields(): array
    {
        return [
            'name' => ['type' => 'text', 'required' => true, 'placeholder' => 'e.g. new feature'],
            'points' => ['type' => 'number', 'required' => true, 'placeholder' => 'e.g. 4'],
            // Pick any number of positions; none picked is every position.
            // A searchable multi-select, since a company may have many.
            'positions' => [
                'type' => 'multiselect',
                'name' => 'position_ids',
                'placeholder' => 'All positions',
                'hint' => 'Leave empty for all positions',
                'options' => StaffPosition::excludingAdmin()->orderBy('position_name')->pluck('position_name', 'id')->all(),
            ],
        ];
    }

    public function inlineRoutes(): array
    {
        return [
            'store' => route('project-tags.store'),
            'update' => route('project-tags.update', '__id__'),
        ];
    }

    public function centeredColumns(): array
    {
        return ['points', 'positions', 'usage_count'];
    }

    public function filter(Request $request): LengthAwarePaginator
    {
        return $this->paginateFromRequest(
            app(ProjectTagListQuery::class)->build(),
            $request,
            ['usage_count' => null, 'positions' => null]
        );
    }

    public function listing(Request $request, LengthAwarePaginator $result): array
    {
        // One lookup for every name the page needs, not one per tag.
        $names = StaffPosition::pluck('position_name', 'id');

        $rows = $result->getCollection()->map(function ($tag) use ($names) {
            return [
                'name' => e($tag->name),
                // Trimmed so 0.20 reads as 0.2 and 13.00 as 13.
                'points' => rtrim(rtrim(number_format((float) $tag->points, 2, '.', ''), '0'), '.'),
                'positions' => $tag->positionIds() === []
                    ? '<span class="kpi-muted">All positions</span>'
                    : collect($tag->positionIds())->map(fn ($id) => $names[$id] ?? null)->filter()->sort()
                        ->map(fn ($name) => '<span class="tag-position-pill">'.e($name).'</span>')->implode(' '),
                'usage_count' => $tag->tasks_count,
                'action' => $this->actionButtons($tag),
                '_inline' => [
                    'name' => $tag->name,
                    'points' => (float) $tag->points,
                    'position_ids' => $tag->positionIds(),
                ],
            ];
        })->all();

        return $this->respond($request, $result, $rows);
    }

    private function actionButtons($tag): string
    {
        $edit = $this->tbButton('ri-edit-2-line', 'tb-ac-btn-1', 'Edit', ['id' => $tag->id], 'js-inline-editable');

        return $edit.' '.$this->tbDeleteForm(route('project-tags.destroy', $tag->id));
    }
}
