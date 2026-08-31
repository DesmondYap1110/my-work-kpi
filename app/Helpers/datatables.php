<?php

if (! function_exists('show_datatables')) {
    /**
     * Render the <table> skeleton for an AJAX-driven list, e.g.
     * {!! show_datatables('StaffList') !!} in a Blade view. Headers come
     * from the list class's getTableColumns(); rows are loaded by
     * resources/js/datatables.js against the generic /datatables/listing
     * endpoint.
     */
    function show_datatables(string $class, array $extraParams = []): string
    {
        return \App\Components\Datatables\Datatables::buildHTML($class, $extraParams);
    }
}

if (! function_exists('show_datatable_filter')) {
    /**
     * Render a list class's filter bar (from its filters() method), e.g.
     * {!! show_datatable_filter('StaffList') !!}. Returns '' for list
     * classes with no filters() defined.
     */
    function show_datatable_filter(string $class): string
    {
        return \App\Components\Datatables\Datatables::buildHTMLFilter($class);
    }
}
