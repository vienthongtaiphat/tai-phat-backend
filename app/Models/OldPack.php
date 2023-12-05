<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OldPack extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "code",
        "duration",
        "amount",
        "price",
        "revenue",
    ];
}
