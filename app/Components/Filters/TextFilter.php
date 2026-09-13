<?php

namespace App\Components\Filters;

/**
 * A free-text search box - e.g. a member's name on a list too long to scroll.
 */
class TextFilter extends Filter
{
    public function __construct(
        string $name,
        string $title,
        protected string $placeholder = 'Search',
    ) {
        parent::__construct($name, $title);
    }

    public function getHTML(): string
    {
        return '<div class="input-group mb-0"><label>'.e($this->title).'</label>'
            .'<input type="search" class="form-control" name="'.e($this->name).'" placeholder="'.e($this->placeholder).'"'
            .($this->currentValue() !== '' ? ' value="'.e($this->currentValue()).'"' : '').'></div>';
    }
}
