<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'finance_id',
        'amount',
        'disbursed_at',
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(cashAdvance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'finance_id');
    }
}
