<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExtendPack extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "code",
        "revenue",
        "real_revenue",
    ];
}
