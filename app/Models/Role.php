<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function approvalSteps()
    {
        return $this->hasMany(ApprovalStep::class);
    }

    public function approvalStepRoles()
    {
        return $this->hasMany(ApprovalStepRole::class);
    }

    public static function getSelectOptions()
    {
        return self::orderBy('name')
            ->get()
            ->map(function ($dept) {
                return [
                    'value' => $dept->id,
                    'label' => $dept->name
                ];
            });
    }
}
