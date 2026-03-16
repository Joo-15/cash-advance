<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Approval extends Model
{
    protected $fillable = [
        'cash_advance_id',
        'approval_step_id',
        'approved_by',
        'status',
        'notes',
    ];

    public function approvalStep()
    {
        return $this->belongsTo(ApprovalStep::class);
    }
}
