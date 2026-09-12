{{--
    The fields describing one scored item. Shared by the add and edit forms so
    the two can't drift apart.

    Expects: $item (null when adding), $markListId
--}}
<div class="row">
    <div class="col-lg-4">
        <div class="input-group">
            <label>Item<span>*</span></label>
            <input type="text" class="form-control" name="title" maxlength="255"
                   value="{{ $item?->title }}" placeholder="e.g. Delivered on schedule" required>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="input-group">
            <label>Description</label>
            <input type="text" class="form-control" name="description"
                   value="{{ $item?->description }}" placeholder="How this item is judged">
        </div>
    </div>
    <div class="col-lg-2">
        <div class="input-group">
            <label>Type<span>*</span></label>
            <select class="form-control" name="objective_type" required>
                <option value="" disabled @selected($item === null)>Select</option>
                @foreach (\App\Enums\ObjectiveType::cases() as $type)
                    <option value="{{ $type->value }}" @selected($item?->objective_type === $type)>
                        {{ $type->label() }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-2">
        <div class="input-group">
            <label>Allowed Marks<span>*</span></label>
            {{-- Any values may be used - see public/js/modules/mark-list.js --}}
            <div class="js-mark-list" id="{{ $markListId }}" data-name="allowed_marks"
                 data-marks="{{ json_encode($item ? $item->allowedMarks() : [5, 4, 3, 2, 1]) }}"></div>
        </div>
    </div>
</div>
