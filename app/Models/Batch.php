<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Batch extends Model
{
    use HasFactory;

    protected $table = 'batch';
    protected $fillable = [
        'product_id',
        'manufacturing_date',
        'expiration_date',
        'stock',
        'price',
        'cost'
    ];

    public function product(): HasOne{  
        return $this->hasOne(Product::class, 'id', 'product_id');
    }
}
