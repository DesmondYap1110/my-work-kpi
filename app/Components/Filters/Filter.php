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
}
