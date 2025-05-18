<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'branch_id',
        'branch_name',
        'number_of_members',
        'branch_manager',
        'cell_phone',
        'phone_number',
        'fax_number',
        'account_number',
        'deposit_withdrawal_history',
        'affiliated_members',
        'stop_allowance',
    ];

    protected $casts = [
        'branch_id' => 'string',
        'branch_name' => 'string',
        'number_of_members' => 'integer',
        'branch_manager' => 'string',
        'cell_phone' => 'string',
        'phone_number' => 'string',
        'fax_number' => 'string',
        'account_number' => 'string',
        'deposit_withdrawal_history' => 'string',
        'affiliated_members' => 'string',
        'stop_allowance' => 'boolean',
    ];
}
