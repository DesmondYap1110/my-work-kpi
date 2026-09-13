<?php

/**
 * Explicit allow-list of App\Components\Datatables\* classes reachable via
 * the generic /datatables/listing AJAX endpoint. Deliberately not resolved
 * from `$request->class` unchecked, since that string ends up in an app()
 * container lookup - keeping it to a known list avoids that becoming an
 * arbitrary-class-resolution vector.
 */
return [
    'classes' => [
        'PositionList',
        'TeamList',
        'StaffList',
        'ProjectList',
        'ProjectTaskList',
        'KpiObjectiveList',
        'KpiObjectiveItemList',
        'ManagePendingList',
        'ProjectTagList',
        'AppraisalList',
        'PerformanceBandList',
        'ReviewScheduleList',
    ],
];
