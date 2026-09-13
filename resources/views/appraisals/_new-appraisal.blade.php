{{--
    Start an appraisal: who is being reviewed, and over what period.

    Shared by the Appraisal list and the Form Setup screen, so an appraiser who
    has just finished adjusting the form can open one without going back.

    open-on-error is bound to this form's own fields rather than to "are there
    any errors at all" - Form Setup has a form of its own, and a failed save
    there must not pop this dialog open instead. See
    public/js/modules/modal-errors.js.

    @param $members  active staff, excluding the administrator
--}}
<x-modal id="addAppraisalModal" title="New Appraisal" :action="route('appraisals.store')"
         confirm="Open Appraisal"
         :open-on-error="$errors->hasAny(['staff_id', 'period_from', 'period_to', 'review_date', 'next_assessment_date'])">
    <div class="row">
        <div class="col-lg-12">
            <div class="input-group">
                <label>Member<span>*</span></label>
                <select class="form-control" name="staff_id" required>
                    <option value="">Select Member</option>
                    @foreach ($members as $member)
                        <option value="{{ $member->id }}" @selected(old('staff_id') == $member->id)>
                            {{ $member->staff_name }}@if ($member->position) &middot; {{ $member->position->position_name }} @endif
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-group">
                <label>Review Period From<span>*</span></label>
                <input type="date" class="form-control" name="period_from"
                       value="{{ old('period_from', now()->startOfYear()->format('Y-m-d')) }}" required>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-group">
                <label>Review Period To<span>*</span></label>
                <input type="date" class="form-control" name="period_to"
                       value="{{ old('period_to', now()->format('Y-m-d')) }}" required>
            </div>
        </div>
        <div class="col-lg-12">
            <span id="note-p" class="d-block mb-2">
                Part 2 of the form is built from the work this member did between
                these two dates, so the period decides what is being judged.
            </span>
        </div>
        <div class="col-lg-6">
            <div class="input-group">
                <label>Review Date</label>
                <input type="date" class="form-control" name="review_date"
                       value="{{ old('review_date', now()->format('Y-m-d')) }}">
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-group">
                <label>Next Assessment</label>
                <input type="date" class="form-control" name="next_assessment_date"
                       value="{{ old('next_assessment_date') }}">
            </div>
        </div>
    </div>
</x-modal>
