@extends('layouts.app')

@section('title', 'Items - '.$objective->title)

@section('content')
    <div id="tb-box" class="general-box mb-3">
        <div id="table-padding">
            <p id="tb-title" class="mb-1">{{ $objective->title }}</p>
            <p id="footer-p" class="mb-0">
                {{ $position->position_name }}
                @if ($objective->category)
                    &middot; {{ $objective->category->name }}
                @endif
            </p>
        </div>
    </div>

    <div id="add-btn-div">
        <a href="{{ route('kpi.objectives.index', $position->id) }}" id="general-btn" class="btn2">
            <i class="ri-arrow-left-line"></i>Back to Objectives
        </a>
        <a href="javascript:void(0);" id="general-btn" class="btn1" data-bs-toggle="modal" data-bs-target="#addItemModal">
            <i class="ri-add-fill"></i>Add Item
        </a>
    </div>

    {!! show_datatables('KpiObjectiveItemList', ['oid' => $objective->id]) !!}

    @foreach (['add' => 'Add Item', 'edit' => 'Edit Item'] as $mode => $heading)
        <div class="modal fade" id="{{ $mode }}ItemModal" tabindex="-1"
             @if ($mode === 'edit') data-url-template="{{ route('kpi.objectives.items.update', [$position->id, $objective->id, '__id__']) }}" @endif>
            <div class="modal-dialog" id="md-dialog">
                <div class="modal-content general-box" id="md-content">
                    <form method="POST" @if ($mode === 'add') action="{{ route('kpi.objectives.items.store', [$position->id, $objective->id]) }}" @endif>
                        @csrf
                        @if ($mode === 'edit') @method('PUT') @endif

                        <div class="modal-header">
                            <p id="modal-title">{{ $heading }}</p>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div id="modal-div">
                            <div class="input-group">
                                <label>Item<span>*</span></label>
                                <input type="text" class="form-control" name="title" maxlength="255"
                                       placeholder="e.g. Delivered on schedule" required>
                            </div>
                            <div class="input-group">
                                <label>Description</label>
                                <textarea class="form-control" name="description" rows="2"
                                          placeholder="How this item is judged"></textarea>
                            </div>
                            <div class="input-group">
                                <label>Allowed Marks<span>*</span></label>
                                {{-- Any values may be used - see public/js/modules/mark-list.js --}}
                                <div class="js-mark-list" id="{{ $mode }}-mark-list"
                                     data-name="allowed_marks"
                                     data-marks="{{ json_encode($mode === 'add' ? [2, 1, 0] : []) }}"></div>
                            </div>
                        </div>

                        <div id="modal-btn-div">
                            <a href="javascript:void(0);" id="general-btn" class="btn2" data-bs-dismiss="modal"><i class="ri-close-fill"></i>Cancel</a>
                            <button type="submit" id="general-btn" class="btn1">
                                <i class="ri-check-fill"></i>{{ $mode === 'add' ? 'Add Item' : 'Save Changes' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endforeach
@endsection
