<?php

namespace MunicipalSaas\Shared\Domain\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public string $currency,
        public int $cents,
    ) {
        if ($this->cents < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public static function mxnFromDecimal(float|string $amount): self
    {
        return new self('MXN', (int) round(((float) $amount) * 100));
    }

    public function toDecimal(): string
    {
        return number_format($this->cents / 100, 2, '.', '');
    }
}
