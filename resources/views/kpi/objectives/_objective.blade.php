{{--
    One objective inside a category, with the items scored under it.
    Everything is edited in place - no dialogs.

    Expects: $objective, $position
--}}
@php
    $editId = 'edit-objective-'.$objective->id;
    $headId = 'head-objective-'.$objective->id;
    $addItemId = 'add-item-'.$objective->id;
    $bodyId = 'body-objective-'.$objective->id;

    // Its items go with it, so the dialog names them.
    $itemCount = $objective->infos->count();
    $confirmObjective = $itemCount > 0
        ? 'Delete "'.$objective->title.'"? This also removes its '.$itemCount.' scored '
            .Str::plural('item', $itemCount).'.'
        : 'Delete the objective "'.$objective->title.'"?';
@endphp

<div class="kpi-objective">
    <div class="kpi-objective-head" id="{{ $headId }}">
        <div>
            <p class="kpi-objective-title mb-0">
                {{ $objective->title }}
                {{-- Shown on the heading so a closed objective still says how
                     much is inside it. --}}
                <span class="kpi-count">{{ $objective->infos->count() }}</span>
            </p>
            @if ($objective->description)
                <p class="kpi-objective-desc mb-0">{{ $objective->description }}</p>
            @endif
        </div>
        <span>
            <button type="button" class="tb-ac-btn tb-ac-toggle" title="Show"
                    aria-expanded="false" aria-controls="{{ $bodyId }}"
                    data-collapse="#{{ $bodyId }}">
                <i class="ri-arrow-down-s-line"></i>
            </button>
            <button type="button" class="tb-ac-btn" id="tb-ac-btn-6" title="Add item"
                    data-inline-form="#{{ $addItemId }}">
                <i class="ri-add-line"></i>
            </button>
            <button type="button" class="tb-ac-btn" id="tb-ac-btn-1" title="Edit objective"
                    data-inline-form="#{{ $editId }}" data-inline-hide="#{{ $headId }}">
                <i class="ri-edit-2-line"></i>
            </button>
            <form action="{{ route('kpi.objectives.destroy', [$position->id, $objective->id]) }}"
                  method="POST" class="d-inline js-confirm-delete"
                  data-confirm-title="Delete objective"
                  data-confirm="{{ $confirmObjective }}">
                @csrf @method('DELETE')
                <button type="submit" class="tb-ac-btn" id="tb-ac-btn-2" title="Delete objective">
                    <i class="ri-delete-bin-6-line"></i>
                </button>
            </form>
        </span>
    </div>

    {{-- Edit this objective, in place of its heading --}}
    <form class="js-inline-form kpi-inline-form" id="{{ $editId }}" method="POST"
          action="{{ route('kpi.objectives.update', [$position->id, $objective->id]) }}" hidden>
        @csrf @method('PUT')
        <div class="row">
            <div class="col-lg-4">
                <div class="input-group">
                    <label>Title<span>*</span></label>
                    <input type="text" class="form-control" name="title" maxlength="255"
                           value="{{ $objective->title }}" required>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="input-group">
                    <label>Description</label>
                    <input type="text" class="form-control" name="description"
                           value="{{ $objective->description }}">
                </div>
            </div>
            <div class="col-lg-3">
                <div class="input-group">
                    <label>Category<span>*</span></label>
                    {{-- Every objective lives under a category; the list is
                         built category-first, so there is nowhere else to put
                         one. --}}
                    <select class="form-control" name="category_id" required>
                        @foreach ($categories as $option)
                            <option value="{{ $option->id }}" @selected($objective->category_id === $option->id)>
                                {{ $option->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="kpi-inline-actions">
            <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save</button>
            <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"
               data-inline-restore="#{{ $headId }}"><i class="ri-close-fill"></i>Cancel</a>
        </div>
    </form>

    {{-- Closed by default - see public/js/modules/collapse.js --}}
    <div class="js-collapse" id="{{ $bodyId }}" hidden>
    @forelse ($objective->infos as $item)
        @include('kpi.objectives._item', ['item' => $item, 'objective' => $objective, 'position' => $position])
    @empty
        <p class="kpi-empty mb-0">No items yet - add one to make this objective scoreable.</p>
    @endforelse

    {{-- Add an item to this objective --}}
    <form class="js-inline-form kpi-inline-form" id="{{ $addItemId }}" method="POST"
          action="{{ route('kpi.objectives.items.store', [$position->id, $objective->id]) }}" hidden>
        @csrf
        @include('kpi.objectives._item-fields', ['item' => null, 'markListId' => 'marks-new-'.$objective->id])
        <div class="kpi-inline-actions">
            <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Item</button>
            <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
        </div>
    </form>
    </div>
</div>
