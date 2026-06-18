<?php

namespace MunicipalSaas\Shared\Domain\Exceptions;

use RuntimeException;

final class TenantNotFoundException extends RuntimeException
{
    public static function forHost(string $host): self
    {
        return new self(sprintf('No active municipality was resolved for host [%s].', $host));
    }
}
