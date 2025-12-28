<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_name',
        'description',
        'quantity',
        'keypad',
        'motor_index',
        'low_stock_threshold',
        'is_active'
    ];

    /**
     * Get all logs associated with this item.
     */
    public function logs()
    {
        return $this->hasMany(ItemLog::class);
    }
}
