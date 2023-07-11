<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaleDetails extends Model
{
    use HasFactory;

    protected $table = 'sale_detail';
    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'subtotal'
    ];
}