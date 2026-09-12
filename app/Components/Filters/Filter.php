<?php

namespace App\Components\Filters;

/**
 * Base for the filter-field objects a Datatables list class declares via
 * filters(). Mirrors the MCS project's Components/Filters pattern: each
 * filter knows its own form field name, label, and how to render itself,
 * so list classes just declare *which* filters they have instead of every
 * Blade view hand-rolling its own filter form markup.
 */
abstract class Filter
{
    public function __construct(
        public string $name,
        public string $title,
    ) {
    }

    abstract public function getHTML(): string;

    /**
     * What this filter should show when the page opens.
     *
     * Taken from the query string, so a link that carries a filter -
     * /staff?pid=9 from the position list's "View members" - arrives with the
     * field already set. The datatable reads its first AJAX draw off these
     * fields, so a filter that renders blank is a filter that was ignored.
     */
    protected function currentValue(): string
    {
        $value = request()->query($this->name);

        return is_scalar($value) ? (string) $value : '';
    }
}
