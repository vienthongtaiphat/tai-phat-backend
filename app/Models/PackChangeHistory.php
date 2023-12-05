<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackChangeHistory extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "code",
        "amount",
        "price",
        "pack_price",
        "revenue",
        "created_at",
    ];
}
