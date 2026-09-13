{{--
    Start an appraisal: who is being reviewed, and over what period.

    Shared by the Appraisal list and the Review Schedule.

    open-on-error is bound to this form's own fields rather than to "are there
    any errors at all" - a page may have a form of its own, and a failed save
    there must not pop this dialog open instead. See
    public/js/modules/modal-errors.js.

    @param $members  active staff, excluding the administrator
--}}
<x-modal id="addAppraisalModal" title="New Appraisal" :action="route('appraisals.store')"
         confirm="Open Appraisal"
         :open-on-error="$errors->hasAny(['staff_id', 'period_from', 'period_to', 'review_date', 'appraisal_cycle', 'next_assessment_date'])">
    <div class="row">
        <div class="col-lg-12">
            <div class="input-group">
                <label>Member<span>*</span></label>
                {{-- Each member carries their review cycle, so Next Assessment can
                     be filled in from it - see public/js/modules/appraisal-next-date.js. --}}
                {{-- Members with a draft already open are shown but cannot be
                     picked - see StoreAppraisalRequest::withValidator(). --}}
                @php $draftStaffIds = \App\Models\Assessment::where('status', \App\Enums\AssessmentStatus::Draft)->pluck('staff_id')->all(); @endphp
                <select class="form-control" name="staff_id" required data-next-date-source>
                    <option value="">Select Member</option>
                    {{-- Grouped by team, so a long member list stays findable. --}}
                    @foreach ($members->loadMissing('team')->groupBy(fn ($m) => $m->team->team_name ?? 'No team')->sortKeys() as $teamName => $teamMembers)
                        <optgroup label="{{ $teamName }}">
                            @foreach ($teamMembers as $member)
                                @php $memberCycle = \App\Enums\AppraisalCycle::tryFrom((string) $member->appraisal_cycle) ?? \App\Enums\AppraisalCycle::Manual; @endphp
                                <option value="{{ $member->id }}" data-cycle="{{ $memberCycle->value }}" data-cycle-label="{{ $memberCycle->label() }}" @selected(old('staff_id') == $member->id) @disabled(in_array($member->id, $draftStaffIds))>
                                    {{ $member->staff_name }}@if ($member->position) &middot; {{ $member->position->position_name }} @endif @if (in_array($member->id, $draftStaffIds)) (draft open) @endif
                                </option>
                            @endforeach
                        </optgroup>
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
            <span id="note-p" class="d-block mb-2 appraisal-modal-note">
                Project marks are counted from the work this member did between
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
                {{-- The member's review cycle, shown so a first appraisal can put
                     them on a schedule right here. Saved to the member when the
                     appraisal opens - the same setting as Appraisal > Schedule. --}}
                <label>Review Every</label>
                <select class="form-control" name="appraisal_cycle" data-next-date-cycle>
                    @foreach (\App\Enums\AppraisalCycle::cases() as $cycle)
                        <option value="{{ $cycle->value }}" @selected(old('appraisal_cycle') === $cycle->value)>{{ $cycle->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="input-group">
                <label>Next Assessment</label>
                <input type="date" class="form-control" name="next_assessment_date"
                       value="{{ old('next_assessment_date') }}" data-next-date>
                <span id="note-p" class="d-block appraisal-next-note appraisal-modal-note" data-next-date-note>Filled in from the member's review cycle.</span>
            </div>
        </div>
    </div>
</x-modal>
