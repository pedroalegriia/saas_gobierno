<?php

namespace MunicipalSaas\TrafficFines\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SearchTrafficFineRequest extends FormRequest
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
            'folio' => ['nullable', 'string', 'max:80', 'required_without:plate'],
            'plate' => ['nullable', 'string', 'max:20', 'required_without:folio'],
        ];
    }
}
