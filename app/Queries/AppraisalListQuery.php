<?php

namespace App\Queries;

use App\Models\Assessment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AppraisalListQuery
{
    public function forRequest(Request $request): Builder
    {
        return Assessment::query()
            // The score column totals each row, so the pieces it totals are
            // loaded with the page rather than one query per appraisal.
            ->with(['staff', 'position', 'reviewer', 'scores', 'projectScores',
                'template.sections', 'template.ratings', 'template.bands'])
            ->when($request->filled('staff_id'), fn ($q) => $q->where('staff_id', $request->integer('staff_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            // An appraisal covers a span, so "in this period" means the two
            // spans overlap - not that it starts inside the filter.
            ->when($request->filled('period_from'), fn ($q) => $q->whereDate('period_to', '>=', $request->date('period_from')))
            ->when($request->filled('period_to'), fn ($q) => $q->whereDate('period_from', '<=', $request->date('period_to')))
            ->latest('id');
    }
}
