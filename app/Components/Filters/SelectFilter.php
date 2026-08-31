<?php

namespace App\Components\Filters;

class SelectFilter extends Filter
{
    /**
     * @param  array<int|string, string>  $options  value => label
     */
    public function __construct(
        string $name,
        string $title,
        protected array $options,
        protected string $placeholder = 'All'
    ) {
        parent::__construct($name, $title);
    }

    public function getHTML(): string
    {
        $options = collect($this->options)
            ->map(fn ($label, $value) => '<option value="'.e($value).'">'.e($label).'</option>')
            ->implode('');

        return '<div class="input-group mb-0"><label>'.e($this->title).'</label>'
            .'<select class="form-control" name="'.e($this->name).'">'
            .'<option value="">'.e($this->placeholder).'</option>'.$options
            .'</select></div>';
    }
}
