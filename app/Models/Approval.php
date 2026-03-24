<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'approval_step_id',
        'approved_by',
        'approved_at',
        'status',
        'notes',
    ];

    public function cashAdvance()
    {
        return $this->belongsTo(CashAdvance::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvalStep()
    {
        return $this->belongsTo(ApprovalStep::class);
    }
}
