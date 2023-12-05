<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pack extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "code",
        "duration",
        "amount",
        "pack_price",
        "price",
        "revenue",
        "description",
    ];
}
