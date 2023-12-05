<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CtvUser extends Model
{
    use HasFactory;

    protected $hidden = ['password'];
    public $timestamps = true;
    protected $fillable = [
        'phone',
        'email',
        'password',
        'branch_id',
        'is_used',
    ];

    public function getTotalAttribute()
    {
        return $this::count();
    }
}