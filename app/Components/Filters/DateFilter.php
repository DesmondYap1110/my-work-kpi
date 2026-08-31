<?php

namespace App\Components\Filters;

class DateFilter extends Filter
{
    public function getHTML(): string
    {
        return '<div class="input-group mb-0"><label>'.e($this->title).'</label>'
            .'<input type="date" class="form-control" name="'.e($this->name).'"></div>';
    }
}
