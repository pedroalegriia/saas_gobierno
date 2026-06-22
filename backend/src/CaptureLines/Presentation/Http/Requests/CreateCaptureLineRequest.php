<?php

namespace MunicipalSaas\CaptureLines\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;
use MunicipalSaas\Shared\Domain\Enums\ServiceType;

final class CreateCaptureLineRequest extends FormRequest
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
            'service_type' => ['required', new Enum(ServiceType::class)],
            'service_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
