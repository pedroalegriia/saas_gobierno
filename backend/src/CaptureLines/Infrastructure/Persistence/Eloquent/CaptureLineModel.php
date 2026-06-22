<?php

namespace MunicipalSaas\CaptureLines\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class CaptureLineModel extends Model
{
    protected $table = 'capture_lines';

    protected $fillable = [
        'municipality_id',
        'folio',
        'service_type',
        'service_id',
        'amount',
        'expiration_date',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expiration_date' => 'date',
    ];
}
