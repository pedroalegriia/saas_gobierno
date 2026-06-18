<?php

namespace MunicipalSaas\Predial\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SearchPredialAccountRequest extends FormRequest
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
            'property_key' => ['required', 'string', 'max:80'],
        ];
    }
}
