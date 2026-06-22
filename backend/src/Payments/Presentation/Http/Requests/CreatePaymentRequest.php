<?php

namespace MunicipalSaas\Payments\Presentation\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreatePaymentRequest extends FormRequest
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
            'capture_line_folio' => ['required', 'string', 'max:40'],
            'gateway' => ['required', Rule::in(['openpay', 'mercadopago', 'stripe'])],
            'method' => ['required', Rule::in(['credit_card', 'debit_card', 'spei', 'oxxo_cash'])],
            'payment_token' => ['required_unless:method,oxxo_cash', 'nullable', 'string', 'max:255'],
        ];
    }
}
