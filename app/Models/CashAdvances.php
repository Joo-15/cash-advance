<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashAdvances extends Model
{
    protected $fillable = [
        'amount',
        'purpose',
        'attachment',
        'status',
    ];
}
