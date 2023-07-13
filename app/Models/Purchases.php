<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Purchases extends Model
{
    use HasFactory;

    protected $table = 'purchases';
    protected $fillable = [
        'supplier_id',
        'date',
        'total'
    ];

    public function supplier(): HasOne {
        return $this->hasOne(Supplier::class, 'id', 'supplier_id');
    }
}