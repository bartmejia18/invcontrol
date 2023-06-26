<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseDetail extends Model
{
    use HasFactory;

    protected $table = 'purchases_detail';
    protected $fillable = [
        'purchase_id',
        'batch_id'
    ];
}