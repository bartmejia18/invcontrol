<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Product extends Model
{
    use HasFactory;

    protected $table = 'product';
    protected $fillable = [
        'name',
        'brand_id',
        'price',
        'presentation_id',
        'unit_measurement_id',
        'image',
        'status'
    ];

    public function brand(): HasOne {
        return $this->hasOne(Brand::class, 'id', 'brand_id');
    }

    public function presentation(): HasOne {
        return $this->hasOne(Presentation::class, 'id', 'presentation_id');
    }

    public function unitMeasurement(): HasOne {
        return $this->hasOne(UnitMeasurement::class, 'id', 'unit_measurement_id');
    }

    public function batchs(): HasMany {
        return $this->hasMany(Batch::class, 'product_id', 'id');
    }
}