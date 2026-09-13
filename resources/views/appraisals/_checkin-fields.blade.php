{{--
    Add/edit fields for one monthly check-in. Shared by both forms in
    appraisals/_checkins.blade.php.

    @param $checkin  the one being edited, or null when adding
    @param $restore  selector of the display row to bring back on cancel
--}}
<div class="row">
    <div class="col-lg-4">
        <div class="input-group">
            <label>Period of Review (From)</label>
            <input type="date" class="form-control" name="period_from"
                   value="{{ optional($checkin?->period_from)->format('Y-m-d') }}">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="input-group">
            <label>Period of Review (To)</label>
            <input type="date" class="form-control" name="period_to"
                   value="{{ optional($checkin?->period_to)->format('Y-m-d') }}">
        </div>
    </div>
    <div class="col-lg-4">
        <div class="input-group">
            <label>Date of Review</label>
            <input type="date" class="form-control" name="review_date"
                   value="{{ optional($checkin?->review_date)->format('Y-m-d') }}">
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Feedback by reviewer</label>
            <textarea class="form-control" name="reviewer_feedback" rows="3">{{ $checkin?->reviewer_feedback }}</textarea>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="input-group">
            <label>Comments by reviewee</label>
            <textarea class="form-control" name="reviewee_comments" rows="3">{{ $checkin?->reviewee_comments }}</textarea>
        </div>
    </div>
</div>

<div id="form-btn-div">
    <button type="button" id="general-btn" class="btn2 js-inline-form-cancel"
            @if ($restore ?? null) data-inline-restore="{{ $restore }}" @endif><i class="ri-close-fill"></i>Cancel</button>
    <button type="submit" id="general-btn" class="btn1"><i class="ri-check-fill"></i>{{ $checkin ? 'Save Check-in' : 'Add Check-in' }}</button>
</div>
