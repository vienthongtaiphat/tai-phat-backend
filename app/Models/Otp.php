<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "user_name",
        "msisdn",
        "resp_content",
        "status",
        "package_name",
        "congtacvien_id",
    ];
}
