<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = [
        'name',
    ];

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

    public function users()
    {
        return $this->hasMany(User::class, 'department_id');
    }
}
