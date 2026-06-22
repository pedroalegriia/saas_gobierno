<?php

namespace MunicipalSaas\Shared\Domain\Enums;

enum CaptureLineStatus: string
{
    case Pending = 'PENDING';
    case Paid = 'PAID';
    case Expired = 'EXPIRED';
    case Cancelled = 'CANCELLED';
}
