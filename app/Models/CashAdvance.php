<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashAdvance extends Model
{
    use HasFactory; // Fitur untuk data Dummy

    protected $fillable = [
        'user_id',
        'tanggal',
        'keperluan',
        'jumlah',
        'status',
    ];

    // Mutator
    public function setTanggalAttribute($value)
    {
        if (!$value) {
            $this->attributes['tanggal'] = null;
            return;
        }

        $this->attributes['tanggal'] = Carbon::createFromTimestampMs($value)
            ->setTimezone('Asia/Jakarta')
            ->format('Y-m-d');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
