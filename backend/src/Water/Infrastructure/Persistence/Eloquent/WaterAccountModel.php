<?php

namespace MunicipalSaas\Water\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

final class WaterAccountModel extends Model
{
    protected $table = 'water_accounts';

    protected $fillable = [
        'municipality_id',
        'contract_number',
        'customer_name',
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
