@extends('layouts.app')

@section('title', 'KPI Objectives - '.$position->position_name)

@php
    // A category with no objectives for this position still appears, so the
    // flow is: create the category, then fill it.
    $uncategorised = $objectivesByCategory->get(null, collect());
@endphp

@section('content')
    <div id="tb-box" class="general-box mb-3">
        <div id="table-padding" class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <p id="tb-title" class="mb-1">{{ $position->position_name }}</p>
                <p id="footer-p" class="mb-0">
                    A category holds objectives, and each objective holds the items that get scored.
                </p>
            </div>
            <div>
                <a href="{{ route('positions.index') }}" id="general-btn" class="btn2">
                    <i class="ri-arrow-left-line"></i>Back to Position
                </a>
                <a href="javascript:void(0);" id="general-btn" class="btn1" data-inline-form="#add-category">
                    <i class="ri-add-fill"></i>Add Category
                </a>
            </div>
        </div>

        {{-- Inline rather than a dialog, so the structure stays visible while
             you type into it. --}}
        <form class="js-inline-form kpi-inline-form" id="add-category" method="POST"
              action="{{ route('kpi-categories.store') }}" hidden>
            @csrf
            <div class="row">
                <div class="col-lg-6">
                    <div class="input-group">
                        <label>Category Name<span>*</span></label>
                        <input type="text" class="form-control" name="name" maxlength="255"
                               placeholder="e.g. Soft Skill, Technical Skill, Service" required>
                    </div>
                </div>
            </div>
            <div class="kpi-inline-actions">
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Category</button>
                <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
            </div>
        </form>
    </div>

    @forelse ($categories as $category)
        @php
            $objectives = $objectivesByCategory->get($category->id, collect());
            $headId = 'head-category-'.$category->id;
            $editId = 'edit-category-'.$category->id;
            $addId = 'add-objective-'.$category->id;
        @endphp

        <div id="tb-box" class="general-box mb-3 kpi-group">
            <div id="table-padding">
                <div class="kpi-group-head" id="{{ $headId }}">
                    <p id="tb-title" class="mb-0">
                        <i class="ri-price-tag-3-line"></i>{{ $category->name }}
                        <span class="kpi-count">{{ $objectives->count() }}</span>
                    </p>
                    <span>
                        <button type="button" class="tb-ac-btn" id="tb-ac-btn-6" title="Add objective"
                                data-inline-form="#{{ $addId }}">
                            <i class="ri-add-line"></i>
                        </button>
                        <button type="button" class="tb-ac-btn" id="tb-ac-btn-1" title="Rename category"
                                data-inline-form="#{{ $editId }}" data-inline-hide="#{{ $headId }}">
                            <i class="ri-edit-2-line"></i>
                        </button>
                        <form action="{{ route('kpi-categories.destroy', $category->id) }}" method="POST"
                              class="d-inline js-confirm-delete">
                            @csrf @method('DELETE')
                            <button type="submit" class="tb-ac-btn" id="tb-ac-btn-2" title="Delete category">
                                <i class="ri-delete-bin-6-line"></i>
                            </button>
                        </form>
                    </span>
                </div>

                <form class="js-inline-form kpi-inline-form" id="{{ $editId }}" method="POST"
                      action="{{ route('kpi-categories.update', $category->id) }}" hidden>
                    @csrf @method('PUT')
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="input-group">
                                <label>Category Name<span>*</span></label>
                                <input type="text" class="form-control" name="name" maxlength="255"
                                       value="{{ $category->name }}" required>
                            </div>
                        </div>
                    </div>
                    <div class="kpi-inline-actions">
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save</button>
                        <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"
                           data-inline-restore="#{{ $headId }}"><i class="ri-close-fill"></i>Cancel</a>
                    </div>
                </form>

                @forelse ($objectives as $objective)
                    @include('kpi.objectives._objective', ['objective' => $objective, 'position' => $position])
                @empty
                    <p class="kpi-empty mb-0">No objectives in this category yet.</p>
                @endforelse

                {{-- Add an objective straight into this category --}}
                <form class="js-inline-form kpi-inline-form" id="{{ $addId }}" method="POST"
                      action="{{ route('kpi.objectives.store', $position->id) }}" hidden>
                    @csrf
                    <input type="hidden" name="category_id" value="{{ $category->id }}">
                    <div class="row">
                        <div class="col-lg-5">
                            <div class="input-group">
                                <label>Objective Title<span>*</span></label>
                                <input type="text" class="form-control" name="title" maxlength="255"
                                       placeholder="e.g. Delivery, Food Quality" required>
                            </div>
                        </div>
                        <div class="col-lg-7">
                            <div class="input-group">
                                <label>Description</label>
                                <input type="text" class="form-control" name="description"
                                       placeholder="What this objective covers">
                            </div>
                        </div>
                    </div>
                    <div class="kpi-inline-actions">
                        <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Objective</button>
                        <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    @empty
        <div id="tb-box" class="general-box mb-3">
            <div id="table-padding">
                <p class="kpi-empty mb-0">
                    Nothing here yet. Add a category first, then the objectives that belong to it.
                </p>
            </div>
        </div>
    @endforelse

    @if ($uncategorised->isNotEmpty())
        <div id="tb-box" class="general-box mb-3 kpi-group">
            <div id="table-padding">
                <div class="kpi-group-head">
                    <p id="tb-title" class="mb-0">
                        <i class="ri-price-tag-3-line"></i>Uncategorised
                        <span class="kpi-count">{{ $uncategorised->count() }}</span>
                    </p>
                </div>

                @foreach ($uncategorised as $objective)
                    @include('kpi.objectives._objective', ['objective' => $objective, 'position' => $position])
                @endforeach
            </div>
        </div>
    @endif
@endsection
