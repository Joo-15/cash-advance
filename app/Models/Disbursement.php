<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'finance_id',
        'amount',
        'total_spent',
        'report_notes',
        'report_status',
        'finance_notes',
        'disbursed_at',
        'submitted_at',
        'approved_at'
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
