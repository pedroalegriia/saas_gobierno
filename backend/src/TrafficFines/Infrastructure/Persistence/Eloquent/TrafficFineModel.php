<?php

namespace MunicipalSaas\TrafficFines\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class TrafficFineModel extends Model
{
    protected $table = 'traffic_fines';

    protected $fillable = [
        'municipality_id',
        'folio',
        'plate',
        'offender_name',
        'amount',
        'status',
        'violation_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'violation_date' => 'date',
    ];
}
