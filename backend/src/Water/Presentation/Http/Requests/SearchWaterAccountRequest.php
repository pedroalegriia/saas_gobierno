<?php

namespace MunicipalSaas\Water\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SearchWaterAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'contract_number' => ['required', 'string', 'max:80'],
        ];
    }
}
