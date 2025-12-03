<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemLog extends Model
{
    use HasFactory;

    // Mass assignable fields
    protected $fillable = [
        'item_id',
        'user_id',
        'quantity_change',
        'log_type',
    ];

    /**
     * The item that this log belongs to.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * The user who performed the action.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
