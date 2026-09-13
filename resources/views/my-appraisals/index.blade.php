@extends('layouts.app')

@section('title', 'My Appraisal')

@section('content')
    <div id="form-box" class="general-box">
        <p id="form-sub-title">My Appraisal</p>
        <p id="footer-p" class="mb-3">
            Reviews your appraiser has completed and handed over. One still being
            written does not appear here.
        </p>

        <div id="table-div">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Review Period</th>
                        <th>Position</th>
                        <th>Appraiser</th>
                        <th class="text-center">Reviewed</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($appraisals as $appraisal)
                        <tr>
                            <td>
                                <a href="{{ route('my.appraisals.show', $appraisal->id) }}" id="tb-link">
                                    {{ $appraisal->periodLabel() }}
                                </a>
                            </td>
                            <td>{{ $appraisal->position->position_name ?? '-' }}</td>
                            <td>{{ $appraisal->reviewer->staff_name ?? '-' }}</td>
                            <td class="text-center">{{ $appraisal->review_date?->format('d M Y') ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('my.appraisals.show', $appraisal->id) }}"
                                   class="tb-ac-btn" id="tb-ac-btn-4" title="Read this appraisal">
                                    <i class="ri-file-list-3-line"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">No Record</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
