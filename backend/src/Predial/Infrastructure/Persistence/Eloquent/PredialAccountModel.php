<?php

namespace MunicipalSaas\Predial\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class PredialAccountModel extends Model
{
    protected $table = 'predial_accounts';

    protected $fillable = [
        'municipality_id',
        'property_key',
        'owner_name',
        'address',
        'current_balance',
        'overdue_balance',
        'status',
    ];

    protected $casts = [
        'current_balance' => 'decimal:2',
        'overdue_balance' => 'decimal:2',
    ];
}
