<?php

namespace MunicipalSaas\Payments\Infrastructure\Gateways;

use RuntimeException;

final readonly class OpenPayApiClient
{
    public function enabled(): bool
    {
        return (bool) config('payments.openpay.enabled')
            && filled(config('payments.openpay.merchant_id'))
            && filled(config('payments.openpay.private_key'));
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function createCharge(array $payload): array
    {
        return $this->request('POST', '/charges', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function request(string $method, string $path, array $payload): array
    {
        if (! $this->enabled()) {
            throw new RuntimeException('OpenPay real mode is not enabled or credentials are missing.');
        }

        $url = $this->baseUrl() . '/' . config('payments.openpay.merchant_id') . $path;
        $curl = curl_init($url);
        if ($curl === false) {
            throw new RuntimeException('Unable to initialize OpenPay request.');
        }

        curl_setopt_array($curl, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => config('payments.openpay.private_key') . ':',
            CURLOPT_TIMEOUT => 30,
        ]);

        $body = curl_exec($curl);
        $status = (int) curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($body === false || $error !== '') {
            throw new RuntimeException('OpenPay request failed: ' . $error);
        }

        $decoded = json_decode((string) $body, true);
        if (! is_array($decoded)) {
            throw new RuntimeException('OpenPay returned an invalid JSON response.');
        }

        if ($status >= 400) {
            throw new RuntimeException('OpenPay error: ' . json_encode($decoded));
        }

        return $decoded;
    }

    private function baseUrl(): string
    {
        return rtrim((string) (
            config('payments.openpay.sandbox')
                ? config('payments.openpay.sandbox_url')
                : config('payments.openpay.production_url')
        ), '/');
    }
}
