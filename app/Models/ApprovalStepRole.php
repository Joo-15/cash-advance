<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStepRole extends Model
{
    protected $fillable = [
        'approval_step_id',
        'role_id',
    ];

    public function approvals()
    {
        return $this->belongsToMany(Approval::class, 'approval_step_id');
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function ApprovalStep()
    {
        return $this->belongsTo(ApprovalStep::class);
    }
}
