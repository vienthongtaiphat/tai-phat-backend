<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupMonthRevenue extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "i24h_balance",
        "ez_balance",
        "month",
        "year",
        "branch_id",
    ];
}
