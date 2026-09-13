{{--
    One part of the appraisal form.

    Both kinds render the same two-column layout - Employee and Reviewer, as
    the paper form has - and differ only in where their rows come from: a
    rating part from the member's position, a project part from the work they
    did in the review period.

    @param array $part      one entry of AssessmentScoreService::summary()['sections']
    @param mixed $scale     the template's ratings, highest first
    @param int   $max       top of the scale
    @param bool  $readOnly
--}}
@php
    $section = $part['section'];
    $weight = rtrim(rtrim(number_format($section->weight(), 2), '0'), '.');
@endphp

<div id="form-box" class="general-box">
    <div class="appraisal-section-head">
        <p id="form-sub-title" class="mb-0">{{ $section->title }}</p>
        <span class="appraisal-weight">{{ $weight }}% of the final score</span>
    </div>

    @if ($section->isProject())
        {{-- The form asks the appraiser to "list out the confirmation KPIs,
             along with the projects involved and the results achieved". The
             system already knows all three, so it writes the lines itself. --}}
        <p id="footer-p" class="mb-3">
            The confirmation KPIs, with the projects involved and the results achieved
            &mdash; built from the work {{ $appraisal->staff->staff_name ?? 'this member' }}
            was assigned between {{ $appraisal->periodLabel() }}. Change the review period
            above and save to rebuild it.
        </p>

        <div id="table-div">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Job Description</th>
                        <th class="text-center appraisal-mark-col">Employee</th>
                        <th class="text-center appraisal-mark-col">Reviewer</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($part['rows'] as $row)
                        <tr>
                            <td>{{ $row->description }}</td>
                            <td class="text-center">
                                <x-appraisal-mark name="project_employee[{{ $row->id }}]" :value="$row->employee_score"
                                                  :scale="$scale" :read-only="$readOnly" />
                            </td>
                            <td class="text-center">
                                <x-appraisal-mark name="project_reviewer[{{ $row->id }}]" :value="$row->reviewer_score"
                                                  :scale="$scale" :read-only="$readOnly" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center">
                                No work assigned in this period, so this part is not scored.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if ($part['rows']->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td class="text-end"><strong>{{ $section->title }} total</strong></td>
                            <td colspan="2" class="text-center">
                                <strong>{{ $part['earned'] }}</strong> / {{ $part['printed_max'] }}
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    @else
        @forelse ($part['groups'] as $group)
            <div id="table-div" class="mb-3">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>{{ $group['category']->name }}</th>
                            <th class="text-center appraisal-mark-col">Employee</th>
                            <th class="text-center appraisal-mark-col">Reviewer</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $lastObjective = null; @endphp
                        @foreach ($group['rows'] as $row)
                            @if ($row['objective'] !== $lastObjective)
                                <tr>
                                    <td colspan="3" id="tb-sub-til">{{ $row['objective'] }}</td>
                                </tr>
                                @php $lastObjective = $row['objective']; @endphp
                            @endif
                            <tr>
                                <td>{{ $row['info']->title }}</td>
                                <td class="text-center">
                                    <x-appraisal-mark name="employee[{{ $row['info']->id }}]" :value="$row['employee_score']"
                                                      :scale="$scale" :read-only="$readOnly" />
                                </td>
                                <td class="text-center">
                                    <x-appraisal-mark name="reviewer[{{ $row['info']->id }}]" :value="$row['reviewer_score']"
                                                      :scale="$scale" :read-only="$readOnly" />
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
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
                </table>
            </div>
        @empty
            <p id="footer-p" class="mb-0">
                This member&rsquo;s position has no measurements set up yet, so there is
                nothing to rate here. Add them under KPI for
                {{ $appraisal->position->position_name ?? 'the position' }}.
            </p>
        @endforelse
    @endif
</div>
