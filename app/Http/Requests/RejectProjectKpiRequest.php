<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class RejectProjectKpiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mark' => ['required', 'integer'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var \App\Models\ProjectKpi $projectKpi */
            $projectKpi = $this->route('project_kpi');

            if (! in_array($this->integer('mark'), $projectKpi->allowedMarksExcludingCurrent(), true)) {
                $validator->errors()->add('mark', 'That mark is not a legally allowed override for this objective.');
            }
        });
    }
}
