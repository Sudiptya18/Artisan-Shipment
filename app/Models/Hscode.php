<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hscode extends Model
{
    use HasFactory;

    protected $fillable = [
        'hscode',
        'description',
        'cd',
        'rd',
        'sd',
        'vat',
        'ait',
        'at',
        'exd',
        'tti',
        'min_ass_value',
    ];

    protected $casts = [
        'cd' => 'decimal:2',
        'rd' => 'decimal:2',
        'sd' => 'decimal:2',
        'vat' => 'decimal:2',
        'ait' => 'decimal:2',
        'at' => 'decimal:2',
        'exd' => 'decimal:2',
        'tti' => 'decimal:2',
        'min_ass_value' => 'decimal:2',
    ];
}

