<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductUnit extends Model
{
    use HasFactory;

    protected $table = 'product_unit';
    protected $fillable = [
        'product_id',
        'unit_measurement_id',
        'price'
    ];

    public function unitMeasurement(): HasOne {
        return $this->hasOne(UnitMeasurement::class, 'id','unit_measurement_id');
    }
}