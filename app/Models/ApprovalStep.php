<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'role_id',
        'step_order',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }
}
