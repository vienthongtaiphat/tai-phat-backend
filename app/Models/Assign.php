<?php

namespace App\Models;

use App\Models\FileUploadedHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assign extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "user_id",
        "file_id",
    ];

    public function getTotalAttribute()
    {
        return $this::count();
    }

    public function file()
    {
        return $this->hasOne(FileUploadedHistory::class, 'id', 'file_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }
}
