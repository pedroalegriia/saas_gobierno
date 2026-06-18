<?php

namespace MunicipalSaas\Shared\Domain\Enums;

enum PaymentStatus: string
{
    case Pending = 'PENDING';
    case Authorized = 'AUTHORIZED';
    case Paid = 'PAID';
    case Failed = 'FAILED';
    case Refunded = 'REFUNDED';
}
