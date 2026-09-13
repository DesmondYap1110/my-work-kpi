@extends('layouts.app')

@section('title', 'My Appraisal')

@section('content')
    <div id="form-box" class="general-box">
        <p id="form-sub-title">My Appraisal</p>
        <p id="footer-p" class="mb-3">
            While an appraisal is a draft, fill in your own marks in the Employee
            column. Once your appraiser generates it, you can read the full review.
        </p>

        <div id="table-div">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Review Period</th>
                        <th>Position</th>
                        <th>Appraiser</th>
                        <th class="text-center">Status</th>
                        <th class="text-center">Reviewed</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appraisals as $appraisal)
                        @php $generated = $appraisal->isGenerated(); @endphp
                        <tr>
                            <td>
                                <a href="{{ route('my.appraisals.show', $appraisal->id) }}" id="tb-link">
                                    {{ $appraisal->periodLabel() }}
                                </a>
                            </td>
                            <td>{{ $appraisal->position->position_name ?? '-' }}</td>
                            <td>{{ $appraisal->reviewer->staff_name ?? '-' }}</td>
                            <td class="text-center">
                                @if ($generated)
                                    <span class="tb-status" id="tb-status-1">Generated</span>
                                @else
                                    <span class="tb-status" id="tb-status-3">Self-assessment</span>
                                @endif
                            </td>
                            <td class="text-center">{{ $generated ? ($appraisal->review_date?->format('d M Y') ?? '-') : '-' }}</td>
                            <td class="text-center">
                                @if ($generated)
                                    <a href="{{ route('my.appraisals.show', $appraisal->id) }}"
                                       class="tb-ac-btn" id="tb-ac-btn-4" title="Read this appraisal">
                                        <i class="ri-file-list-3-line"></i>
                                    </a>
                                @else
                                    <a href="{{ route('my.appraisals.show', $appraisal->id) }}"
                                       class="tb-ac-btn" id="tb-ac-btn-1" title="Fill in your marks">
                                        <i class="ri-edit-2-line"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No Record</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
