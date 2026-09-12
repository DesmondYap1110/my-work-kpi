{{--
    One scored item, shown as a row and edited in place.

    Expects: $item, $objective, $position
--}}
@php
    $rowId = 'item-row-'.$item->id;
    $editId = 'edit-item-'.$item->id;
@endphp

<div class="kpi-item" id="{{ $rowId }}">
    <div>
        <span class="kpi-item-title">{{ $item->title }}</span>
        @if ($item->description)
            <span class="kpi-item-desc">{{ $item->description }}</span>
        @endif
    </div>
    <div class="kpi-item-meta">
        <span class="kpi-marks">
            {{ $item->allowedMarks()
                ? implode(', ', array_map(fn ($m) => ($m > 0 ? '+' : '').$m, $item->allowedMarks()))
                : 'no marks' }}
        </span>
        <button type="button" class="tb-ac-btn" id="tb-ac-btn-1" title="Edit item"
                data-inline-form="#{{ $editId }}" data-inline-hide="#{{ $rowId }}">
            <i class="ri-edit-2-line"></i>
        </button>
        <form action="{{ route('kpi.objectives.items.destroy', [$position->id, $objective->id, $item->id]) }}"
              method="POST" class="d-inline js-confirm-delete"
              data-confirm-title="Delete item"
              data-confirm="Delete the item &quot;{{ $item->title }}&quot;?">
            @csrf @method('DELETE')
            <button type="submit" class="tb-ac-btn" id="tb-ac-btn-2" title="Delete item">
                <i class="ri-delete-bin-6-line"></i>
            </button>
        </form>
    </div>
</div>

<form class="js-inline-form kpi-inline-form" id="{{ $editId }}" method="POST"
      action="{{ route('kpi.objectives.items.update', [$position->id, $objective->id, $item->id]) }}" hidden>
    @csrf @method('PUT')
    @include('kpi.objectives._item-fields', ['item' => $item, 'markListId' => 'marks-'.$item->id])
    <div class="kpi-inline-actions">
        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save</button>
        <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"
           data-inline-restore="#{{ $rowId }}"><i class="ri-close-fill"></i>Cancel</a>
    </div>
</form>
