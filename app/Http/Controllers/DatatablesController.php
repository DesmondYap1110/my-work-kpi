<?php

namespace App\Http\Controllers;

use App\Components\Datatables\Datatables;
use App\Http\Requests\DatatableRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Single generic AJAX endpoint serving every module's list table, mirroring
 * the MCS project's Datatables pattern: the concrete {class}List handles
 * its own filtering/sorting/paging (via app/Queries/*) and row formatting,
 * this controller just wires the request through to it.
 */
class DatatablesController extends Controller
{
    public function listing(DatatableRequest $request): JsonResponse
    {
        /** @var Datatables $object */
        $object = app("\\App\\Components\\Datatables\\{$request->string('class')}");

        // The class name arrives in the request, so the authorization the
        // routes do for pages has to be repeated here for their rows -
        // otherwise hiding a page would hide only its link.
        if ($object::adminOnly() && ! Auth::user()?->isAdmin()) {
            abort(403, 'Only the system administrator can view this list.');
        }

        $result = $object->filter($request);

        return response()->json($object->listing($request, $result));
    }
}
