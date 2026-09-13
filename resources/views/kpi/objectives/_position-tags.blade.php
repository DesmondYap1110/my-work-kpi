{{--
    The project tags this position can use - the kinds of work that earn its
    project marks - managed without leaving the position's KPI Setting page.

    Same data as Settings > Project Tag Setting (project_tag.position_ids), seen
    from this position's side: add a new tag just for it, bring in a tag another
    position uses, or take this position off one. See PositionTagController.

    Expects: $position, $positionTags, $otherTags, $positionNames
--}}
@php
    $pts = fn ($n) => rtrim(rtrim(number_format((float) $n, 2, '.', ''), '0'), '.');
@endphp

<div id="form-box" class="general-box mb-3">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
        <div>
            <p id="form-sub-title" class="mb-1 d-flex align-items-center gap-2">
                Project Tags
                {{-- The explanation shows on hover (or keyboard focus), so it
                     stays out of the way - see public/js/modules/help-toggle.js. --}}
                <button type="button" class="kpi-help-btn" aria-label="What are project tags?"
                        data-help-hover="The kinds of work a {{ $position->position_name }} is tagged with. A completed task earns its tag's points towards the project marks above.">
                    <i class="ri-question-line"></i>
                </button>
            </p>
        </div>
        <div>
            <a href="{{ route('project-tags.index') }}" id="general-btn" class="btn2">
                <i class="ri-price-tag-3-line"></i>Manage all tags
            </a>
            <a href="javascript:void(0);" id="general-btn" class="btn1" data-inline-form="#add-position-tag">
                <i class="ri-add-fill"></i>Add Tag
            </a>
        </div>
    </div>

    {{-- Inline, like Add Category: pick a tag another position already uses,
         or create a new one for this position only. --}}
    <form class="js-inline-form kpi-inline-form" id="add-position-tag" method="POST"
          action="{{ route('kpi.tags.store', $position->id) }}" @unless ($errors->hasAny(['name', 'points'])) hidden @endunless>
        @csrf
        <div class="row">
            @if ($otherTags->isNotEmpty())
                <div class="col-lg-4">
                    <div class="input-group">
                        <label>Use an existing tag</label>
                        <select class="form-control" name="tag_id"
                                onchange="this.form.querySelectorAll('[data-new-tag]').forEach(function (el) { el.disabled = !!this.value; }, this)">
                            <option value="">- Create a new tag instead -</option>
                            @foreach ($otherTags as $tag)
                                <option value="{{ $tag->id }}">
                                    {{ $tag->name }} ({{ $pts($tag->points) }} pts)
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
            <div class="col-lg-4">
                <div class="input-group">
                    <label>New tag name</label>
                    <input type="text" class="form-control" name="name" maxlength="255" value="{{ old('name') }}"
                           placeholder="e.g. code review" data-new-tag>
                    @error('name') <span class="unique-check-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="col-lg-4">
                <div class="input-group">
                    <label>Points</label>
                    <input type="number" class="form-control" name="points" min="0" max="9999" step="0.1"
                           value="{{ old('points') }}" placeholder="e.g. 1.5" data-new-tag>
                    @error('points') <span class="unique-check-feedback">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="kpi-inline-actions">
            <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Tag</button>
            <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
        </div>
    </form>

    @error('tag')
        <p class="unique-check-feedback mb-2">{{ $message }}</p>
    @enderror

    <div id="table-div">
        <table class="table table-bordered align-middle mb-0">
            <thead>
                <tr>
                    <th>Tag</th>
                    <th class="text-center">Points</th>
                    <th class="text-center">Used by</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            {{-- First 5 rows, the rest behind Show all - see show-more.js. --}}
            <tbody data-show-more="5" data-show-more-label="tags">
                @forelse ($positionTags as $tag)
                    @php $ids = $tag->positionIds(); @endphp
                    <tr>
                        <td>{{ $tag->name }}</td>
                        <td class="text-center">{{ $pts($tag->points) }}</td>
                        <td class="text-center">
                            @if ($ids === [])
                                <span class="kpi-muted">All positions</span>
                            @else
                                @foreach (collect($ids)->map(fn ($id) => $positionNames[$id] ?? null)->filter()->sort() as $name)
                                    <span class="tag-position-pill">{{ $name }}</span>
                                @endforeach
                            @endif
                        </td>
                        <td class="text-center">
                            @php
                                $scope = match (true) {
                                    $ids === [] => 'every position',
                                    count($ids) === 1 => 'this position only',
                                    default => count($ids).' positions',
                                };
                            @endphp
                            {{-- Shared with other positions: this position can step off
                                 it without taking it from the others. --}}
                            @if (count($ids) > 1)
                                <form action="{{ route('kpi.tags.destroy', [$position->id, $tag->id]) }}" method="POST"
                                      class="d-inline js-confirm-delete"
                                      data-confirm-title="Remove tag"
                                      data-confirm-label="Remove"
                                      data-confirm="Remove &quot;{{ $tag->name }}&quot; from {{ $position->position_name }}? Other positions keep it.">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="tb-ac-btn" id="tb-ac-btn-3" title="Remove from this position only">
                                        <i class="ri-link-unlink"></i>
                                    </button>
                                </form>
                            @endif
                            {{-- Deletes the tag itself, wherever it is used. Tasks already
                                 tagged keep their points - see ProjectTask::tag(). --}}
                            <form action="{{ route('project-tags.destroy', $tag->id) }}" method="POST"
                                  class="d-inline js-confirm-delete"
                                  data-confirm-title="Delete tag"
                                  data-confirm="Delete &quot;{{ $tag->name }}&quot;? It is used by {{ $scope }} and will no longer be offered on new tasks.{{ $tag->tasks_count ? ' The '.$tag->tasks_count.' task(s) already tagged keep their points.' : '' }}">
                                @csrf @method('DELETE')
                                <button type="submit" class="tb-ac-btn" id="tb-ac-btn-2" title="Delete tag">
                                    <i class="ri-delete-bin-6-line"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center">No tags for this position yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
