<?php

namespace App\Http\Controllers;

use App\Http\Requests\DatatableRequest;
use Illuminate\Http\JsonResponse;

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
        $object = app("\\App\\Components\\Datatables\\{$request->string('class')}");

        $result = $object->filter($request);

        return response()->json($object->listing($request, $result));
    }
}
