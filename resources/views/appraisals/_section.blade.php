{{--
    The KPI objectives on the appraisal form: the position's categories,
    objectives and items, each rated in two columns - Employee and Reviewer - as
    the paper form has. Each item offers its own allowed marks.

    Each column has one owner. The member fills in Employee (their
    self-assessment, from My Appraisal); the appraiser fills in Reviewer. While
    the member is filling in theirs, the Reviewer column and the totals are not
    shown at all - see MyAppraisalController.

    @param array $part            AssessmentScoreService::summary()['objectives']
    @param bool  $employeeReadOnly
    @param bool  $reviewerReadOnly
    @param bool  $selfAssessment  the member filling in their own column
--}}
@php $selfAssessment = $selfAssessment ?? false; @endphp
<div id="form-box" class="general-box">
    <div class="appraisal-section-head">
        <p id="form-sub-title" class="mb-0">KPI Objectives</p>
        <span class="appraisal-weight">{{ $objectivesShare ?? '-' }} of 100 points</span>
    </div>

    @forelse ($part['groups'] as $group)
        <div id="table-div" class="mb-3">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>{{ $group['category']->name }}</th>
                        <th class="text-center appraisal-mark-col">
                            <span class="d-inline-flex align-items-center gap-1">
                                {{ $selfAssessment ? 'Your mark' : 'Employee' }}
                                @unless ($selfAssessment)
                                    <button type="button" class="kpi-help-btn" aria-label="Who fills in the Employee column?"
                                            data-help-hover="Filled in by the member as their self-assessment, from My Appraisal. Only the member can change it.">
                                        <i class="ri-question-line"></i>
                                    </button>
                                @endunless
                            </span>
                        </th>
                        @unless ($selfAssessment)
                            <th class="text-center appraisal-mark-col">Reviewer</th>
                        @endunless
                    </tr>
                </thead>
                <tbody>
                    @php $lastObjective = null; @endphp
                    @foreach ($group['rows'] as $row)
                        @if ($row['objective'] !== $lastObjective)
                            <tr>
                                <td colspan="{{ $selfAssessment ? 2 : 3 }}" id="tb-sub-til">{{ $row['objective'] }}</td>
                            </tr>
                            @php $lastObjective = $row['objective']; @endphp
                        @endif
                        <tr>
                            <td>{{ $row['info']->title }}</td>
                            <td class="text-center">
                                <x-appraisal-mark name="employee[{{ $row['info']->id }}]" :value="$row['employee_score']"
                                                  :marks="$row['marks']" :read-only="$employeeReadOnly" />
                            </td>
                            @unless ($selfAssessment)
                                <td class="text-center">
                                    <x-appraisal-mark name="reviewer[{{ $row['info']->id }}]" :value="$row['reviewer_score']"
                                                      :marks="$row['marks']" :read-only="$reviewerReadOnly" />
                                </td>
                            @endunless
                        </tr>
                    @endforeach
                </tbody>
                @unless ($selfAssessment)
                    <tfoot>
                        <tr>
                            {{-- The printed maximum is every item; the score counts only
                                 the ones that were rated, so a group left blank does not
                                 drag the total down. --}}
                            <td class="text-end"><strong>{{ $group['category']->name }} total</strong></td>
                            <td colspan="2" class="text-center">
                                <strong>{{ $group['earned'] }}</strong> / {{ $group['printed_max'] }}
                            </td>
                        </tr>
                    </tfoot>
                @endunless
            </table>
        </div>
    @empty
        <p id="footer-p" class="mb-0">
            This member&rsquo;s position has no measurements set up yet, so there is
            nothing to rate here. Add them under KPI for
            {{ $appraisal->position->position_name ?? 'the position' }}.
        </p>
    @endforelse
</div>
