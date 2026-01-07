<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityPin extends Model
{
    protected $fillable = ['pin_hash'];
}
