{{--
    The monthly check-ins held during the review period.

    These carry no score. They are the record of what was said at the time,
    which is what makes an "Extend" at the end fair rather than a surprise -
    so they sit before the ratings, as they do on the paper form.

    Each one is its own form, so this block lives outside the form that saves
    the ratings; forms cannot be nested.

    @param $appraisal
    @param bool $readOnly
--}}
<div id="form-box" class="general-box">
    <div class="appraisal-section-head">
        <p id="form-sub-title" class="mb-0">Monthly Check-ins</p>
        @unless ($readOnly)
            <button type="button" id="general-btn" class="btn2" data-inline-form="#add-checkin">
                <i class="ri-add-line"></i>Add Check-in
            </button>
        @endunless
    </div>

    @forelse ($appraisal->checkins as $index => $checkin)
        @php $editId = 'edit-checkin-'.$checkin->id; @endphp

        <div class="appraisal-checkin" id="checkin-{{ $checkin->id }}">
            <div class="appraisal-checkin-head">
                <strong>Check-in {{ $index + 1 }}</strong>
                <span class="kpi-item-desc">
                    @if ($checkin->period_from && $checkin->period_to)
                        {{ $checkin->period_from->format('d M Y') }} &ndash; {{ $checkin->period_to->format('d M Y') }}
                    @else
                        Period not set
                    @endif
                    @if ($checkin->review_date)
                        &middot; Reviewed {{ $checkin->review_date->format('d M Y') }}
                    @endif
                </span>

                @unless ($readOnly)
                    <span class="appraisal-checkin-actions">
                        <button type="button" class="tb-ac-btn" id="tb-ac-btn-1" title="Edit check-in"
                                data-inline-form="#{{ $editId }}" data-inline-hide="#checkin-{{ $checkin->id }}">
                            <i class="ri-edit-2-line"></i>
                        </button>
                        <form action="{{ route('appraisals.checkins.destroy', [$appraisal->id, $checkin->id]) }}"
                              method="POST" class="d-inline js-confirm-delete"
                              data-confirm="Delete this check-in and the feedback recorded in it?">
                            @csrf @method('DELETE')
                            <button type="submit" class="tb-ac-btn" id="tb-ac-btn-2" title="Delete check-in">
                                <i class="ri-delete-bin-6-line"></i>
                            </button>
                        </form>
                    </span>
                @endunless
            </div>

            <p class="appraisal-checkin-label">Feedback by reviewer</p>
            <p id="footer-p" class="appraisal-comments">{{ $checkin->reviewer_feedback ?: 'None recorded.' }}</p>

            <p class="appraisal-checkin-label">Comments by reviewee</p>
            <p id="footer-p" class="appraisal-comments mb-0">{{ $checkin->reviewee_comments ?: 'None recorded.' }}</p>
        </div>

        @unless ($readOnly)
            <div class="js-inline-form" id="{{ $editId }}" hidden>
                <form action="{{ route('appraisals.checkins.update', [$appraisal->id, $checkin->id]) }}" method="POST">
                    @csrf @method('PUT')
                    @include('appraisals._checkin-fields', ['checkin' => $checkin, 'restore' => '#checkin-'.$checkin->id])
                </form>
            </div>
        @endunless
    @empty
        <p id="footer-p" class="mb-0">
            No check-ins recorded{{ $readOnly ? '.' : ' yet. Add one for each month of the review period.' }}
        </p>
    @endforelse

    @unless ($readOnly)
        <div class="js-inline-form mt-3" id="add-checkin" hidden>
            <form action="{{ route('appraisals.checkins.store', $appraisal->id) }}" method="POST">
                @csrf
                @include('appraisals._checkin-fields', ['checkin' => null, 'restore' => null])
            </form>
        </div>
    @endunless
</div>
