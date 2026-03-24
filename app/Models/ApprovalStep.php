<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'step_order',
        'name',
    ];

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approval_step_id');
    }

    public function approvalStepRoles()
    {
        return $this->hasMany(ApprovalStepRole::class);
    }
}
