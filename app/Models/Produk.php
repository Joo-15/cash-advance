<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Produk extends Model
{
    use HasFactory; // Fitur untuk data Dummy

    protected $fillable = [
        'nama',
        'harga',
    ];
}
