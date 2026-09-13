@extends('layouts.app')

@section('title', 'Appraisal Form')

@section('content')
    {{-- Having set the form up, the next thing anyone wants is to use it, so
         a member can be picked from here rather than only from the Appraisal
         list. Same form either way - see appraisals/_new-appraisal. --}}
    <div id="form-box" class="general-box appraisal-start">
        <div>
            <p id="form-sub-title" class="mb-1">Start an Appraisal</p>
            <p id="footer-p" class="mb-0">
                Pick the member and the period to review. The form below is what they
                will be scored against.
            </p>
        </div>
        <button type="button" id="general-btn" class="btn1" data-bs-toggle="modal" data-bs-target="#addAppraisalModal">
            <i class="ri-add-fill"></i>New Appraisal
        </button>
    </div>

    {{--
        Nothing on this screen is fixed in code. A company sets its own parts
        and weightings, its own marks, and its own bands - so each table can be
        added to and removed from, not just edited.

        Editing saves the whole screen at once; adding and removing are their
        own small forms, which is why they sit outside the one below.
    --}}
    <form action="{{ route('appraisal-form.update') }}" method="POST">
        @csrf @method('PUT')

        <div id="form-box" class="general-box">
            <div class="appraisal-section-head">
                <p id="form-sub-title" class="mb-0">Parts &amp; Weighting</p>
                <span class="appraisal-weight">Totalling {{ rtrim(rtrim(number_format($template->sections->sum(fn ($s) => (float) $s->weightage), 2), '0'), '.') }}%</span>
            </div>
            <p id="footer-p" class="mb-3">
                How much of the final score each part carries. A part with nothing to
                score drops out and the rest are re-weighted between them, so a member
                with no project work in the period is judged on the other parts alone
                rather than marked down for it. The weightings need not total 100 &mdash;
                what matters is their proportion to each other.
            </p>

            <div id="table-div">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Part</th>
                            <th>Scored from</th>
                            <th class="text-center appraisal-weight-col">Weighting %</th>
                            <th class="text-center appraisal-row-actions-col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($template->sections as $section)
                            <tr>
                                <td>
                                    <input type="text" class="form-control" required maxlength="255"
                                           name="sections[{{ $section->id }}][title]"
                                           value="{{ old('sections.'.$section->id.'.title', $section->title) }}">
                                </td>
                                <td>
                                    {{ $section->isProject()
                                        ? 'The member\'s project work in the review period'
                                        : 'The KPI categories pointed at this part' }}
                                </td>
                                <td class="text-center">
                                    <input type="number" class="form-control weighting-cell"
                                           name="sections[{{ $section->id }}][weightage]"
                                           min="0" max="100" step="1" required
                                           value="{{ old('sections.'.$section->id.'.weightage', (int) $section->weightage) }}">
                                </td>
                                <td class="text-center">
                                    <x-appraisal-row-delete id="del-part-{{ $section->id }}" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="form-box" class="general-box">
            <p id="form-sub-title">Rating Scale</p>
            <p id="footer-p" class="mb-3">
                The marks an appraiser chooses between, and what each one means. Rate out
                of five, out of ten, or out of three &mdash; the scoring reads the scale
                from here rather than assuming it. A mark already used to score an
                appraisal can be renamed but not removed.
            </p>

            <div id="table-div">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th class="text-center appraisal-weight-col">Mark</th>
                            <th>Name</th>
                            <th>What it means</th>
                            <th class="text-center appraisal-row-actions-col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($template->ratings as $rating)
                            <tr>
                                <td class="text-center">
                                    <input type="number" class="form-control weighting-cell" required
                                           min="0" max="100" step="1"
                                           name="ratings[{{ $rating->id }}][value]"
                                           value="{{ old('ratings.'.$rating->id.'.value', $rating->value) }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" required maxlength="255"
                                           name="ratings[{{ $rating->id }}][label]"
                                           value="{{ old('ratings.'.$rating->id.'.label', $rating->label) }}">
                                </td>
                                <td>
                                    <textarea class="form-control" rows="2" maxlength="1000"
                                              name="ratings[{{ $rating->id }}][description]">{{ old('ratings.'.$rating->id.'.description', $rating->description) }}</textarea>
                                </td>
                                <td class="text-center">
                                    @if (in_array($rating->value, $usedMarks, true))
                                        <span class="appraisal-locked" title="Appraisals have been scored with this mark">
                                            <i class="ri-lock-line"></i>
                                        </span>
                                    @else
                                        <x-appraisal-row-delete id="del-mark-{{ $rating->id }}" />
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="form-box" class="general-box">
            <p id="form-sub-title">Performance Bands</p>
            <p id="footer-p" class="mb-3">
                What a final percentage is called, and what it means for the review. The
                outcome is the consequential half &mdash; it is the word that decides
                whether a probation is confirmed, extended or ended. A score is placed in
                the highest band starting at or below it, so a part-way figure like 79.5
                still lands.
            </p>

            <div id="table-div">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th class="text-center appraisal-weight-col">From %</th>
                            <th class="text-center appraisal-weight-col">To %</th>
                            <th>Band</th>
                            <th>Outcome</th>
                            <th class="text-center appraisal-row-actions-col"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($template->bands as $band)
                            <tr>
                                <td class="text-center">
                                    <input type="number" class="form-control weighting-cell" step="0.01"
                                           min="0" max="100" required
                                           name="bands[{{ $band->id }}][min_score]"
                                           value="{{ old('bands.'.$band->id.'.min_score', (float) $band->min_score) }}">
                                </td>
                                <td class="text-center">
                                    <input type="number" class="form-control weighting-cell" step="0.01"
                                           min="0" max="100" required
                                           name="bands[{{ $band->id }}][max_score]"
                                           value="{{ old('bands.'.$band->id.'.max_score', (float) $band->max_score) }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" required maxlength="255"
                                           name="bands[{{ $band->id }}][label]"
                                           value="{{ old('bands.'.$band->id.'.label', $band->label) }}">
                                </td>
                                <td>
                                    <input type="text" class="form-control" maxlength="255"
                                           name="bands[{{ $band->id }}][outcome]"
                                           value="{{ old('bands.'.$band->id.'.outcome', $band->outcome) }}"
                                           placeholder="Pass / Extend / Fail">
                                </td>
                                <td class="text-center">
                                    <x-appraisal-row-delete id="del-band-{{ $band->id }}" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div id="form-btn-div">
            <a href="{{ route('appraisals.index') }}" id="general-btn" class="btn2"><i class="ri-close-fill"></i>Cancel</a>
            <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Save Form</button>
        </div>
    </form>

    @include('appraisal-form._row-deletes')

    {{-- Adding a row is its own post, so these sit outside the form above. --}}
    <div id="form-box" class="general-box">
        <div class="appraisal-section-head">
            <p id="form-sub-title" class="mb-0">Add to the form</p>
        </div>
        <div class="appraisal-add-row">
            <button type="button" id="general-btn" class="btn2" data-inline-form="#add-part">
                <i class="ri-add-line"></i>Add a Part
            </button>
            <button type="button" id="general-btn" class="btn2" data-inline-form="#add-mark">
                <i class="ri-add-line"></i>Add a Mark
            </button>
            <button type="button" id="general-btn" class="btn2" data-inline-form="#add-band">
                <i class="ri-add-line"></i>Add a Band
            </button>
        </div>

        <form class="js-inline-form kpi-inline-form mt-3" id="add-part" method="POST"
              action="{{ route('appraisal-form.parts.store') }}" hidden>
            @csrf
            <div class="row">
                <div class="col-lg-5">
                    <div class="input-group">
                        <label>Part Name<span>*</span></label>
                        <input type="text" class="form-control" name="title" maxlength="255" required
                               placeholder="e.g. Part 3 - Client Feedback">
                    </div>
                </div>
                <div class="col-lg-4">
                    @php $hasProjectPart = $template->sections->contains(fn ($s) => $s->isProject()); @endphp
                    <div class="input-group">
                        <label>Scored from<span>*</span></label>
                        @if ($hasProjectPart)
                            {{-- There is one set of project rows, so a second
                                 project part would score them twice. --}}
                            <input type="hidden" name="type" value="rating">
                            <input type="text" class="form-control" readonly value="KPI categories pointed at it">
                        @else
                            <select class="form-control" name="type" required>
                                <option value="rating">KPI categories pointed at it</option>
                                <option value="project">The member&rsquo;s project work in the period</option>
                            </select>
                        @endif
                    </div>
                </div>
                <div class="col-lg-3">
                    <div class="input-group">
                        <label>Weighting %<span>*</span></label>
                        <input type="number" class="form-control" name="weightage" min="0" max="100" step="1" value="0" required>
                    </div>
                </div>
            </div>
            <div class="kpi-inline-actions">
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Part</button>
                <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
            </div>
        </form>

        <form class="js-inline-form kpi-inline-form mt-3" id="add-mark" method="POST"
              action="{{ route('appraisal-form.marks.store') }}" hidden>
            @csrf
            <div class="row">
                <div class="col-lg-2">
                    <div class="input-group">
                        <label>Mark<span>*</span></label>
                        <input type="number" class="form-control" name="value" min="0" max="100" step="1" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="input-group">
                        <label>Name<span>*</span></label>
                        <input type="text" class="form-control" name="label" maxlength="255" required
                               placeholder="e.g. Exceptional">
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="input-group">
                        <label>What it means</label>
                        <input type="text" class="form-control" name="description" maxlength="1000">
                    </div>
                </div>
            </div>
            <div class="kpi-inline-actions">
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Mark</button>
                <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
            </div>
        </form>

        <form class="js-inline-form kpi-inline-form mt-3" id="add-band" method="POST"
              action="{{ route('appraisal-form.bands.store') }}" hidden>
            @csrf
            <div class="row">
                <div class="col-lg-2">
                    <div class="input-group">
                        <label>From %<span>*</span></label>
                        <input type="number" class="form-control" name="min_score" min="0" max="100" step="0.01" required>
                    </div>
                </div>
                <div class="col-lg-2">
                    <div class="input-group">
                        <label>To %<span>*</span></label>
                        <input type="number" class="form-control" name="max_score" min="0" max="100" step="0.01" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="input-group">
                        <label>Band<span>*</span></label>
                        <input type="text" class="form-control" name="label" maxlength="255" required>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="input-group">
                        <label>Outcome</label>
                        <input type="text" class="form-control" name="outcome" maxlength="255"
                               placeholder="Pass / Extend / Fail">
                    </div>
                </div>
            </div>
            <div class="kpi-inline-actions">
                <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>Add Band</button>
                <a href="javascript:void(0);" id="general-btn" class="btn2 js-inline-form-cancel"><i class="ri-close-fill"></i>Cancel</a>
            </div>
        </form>
    </div>

    @include('appraisals._new-appraisal')
@endsection
