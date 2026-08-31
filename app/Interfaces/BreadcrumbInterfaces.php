<?php

namespace App\Interfaces;

interface BreadcrumbInterfaces
{
    /**
     * The breadcrumb trail for this controller's pages, following the same
     * pattern used in the MCS project: the controller owns its trail and a
     * view composer feeds it to the layout, so no page view repeats it.
     *
     * The leading dashboard icon is rendered by the layout and must not be
     * included here. Each item is:
     *
     *   ['name' => string, 'route' => string, 'active' => bool]
     *
     * An empty 'route' renders as plain text (a group label, e.g. "project"),
     * and 'active' marks the current page, which is never a link.
     *
     * @return array<int, array{name: string, route: string, active: bool}>
     */
    public function getBreadcrumbs(): array;
}
