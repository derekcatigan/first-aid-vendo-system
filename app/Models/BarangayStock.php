<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangayStock extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'description',
        'quantity',
        'low_stock_threshold',
    ];
}
