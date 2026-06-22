<?php

namespace MunicipalSaas\Payments\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class PaymentModel extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'municipality_id',
        'capture_line_id',
        'gateway',
        'method',
        'amount',
        'reference',
        'status',
        'paid_at',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];
}
