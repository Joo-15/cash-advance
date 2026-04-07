<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApprovalStep extends Model
{
    protected $fillable = [
        'step_order',
    ];

    public static function getSelectOptions()
    {
        return self::orderBy('id')
            ->get()
            ->map(function ($approvalStep) {
                return [
                    'value' => $approvalStep->id,
                    'label' => $approvalStep->step_order
                ];
            });
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class, 'approval_step_id');
    }

    public function approvalStepRoles()
    {
        return $this->hasMany(ApprovalStepRole::class);
    }
}
