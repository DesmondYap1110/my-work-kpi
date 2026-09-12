<?php

namespace App\Queries;

use App\Models\Staff;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class StaffListQuery
{
    public function forRequest(Request $request): Builder
    {
        return Staff::query()
            ->with(['position', 'team'])
            ->excludingAdmin()
            ->when($request->filled('pid'), fn ($q) => $q->where('position_id', $request->integer('pid')))
            ->when($request->filled('teamid'), fn ($q) => $q->where('team_id', $request->integer('teamid')))
            ->latest('id');
    }
}
