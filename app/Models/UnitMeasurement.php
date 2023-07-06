<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnitMeasurement extends Model
{
    use HasFactory;

    protected $table = 'unit_measurement';
    protected $fillable = [
        'unit_measurement',
        'status'
    ];
}