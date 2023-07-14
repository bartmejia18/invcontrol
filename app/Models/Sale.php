<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sale';
    protected $fillable = [
        'customer',
        'date',
        'total',
        'status'
    ];

    public function details(): HasMany {
        return $this->hasMany(SaleDetail::class, 'sale_id', 'id');
    }
}