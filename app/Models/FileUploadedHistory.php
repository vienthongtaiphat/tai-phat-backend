<?php

namespace App\Models;

use App\Models\Assign;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileUploadedHistory extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        'file_name',
        'upload_by',
        'activated',
    ];

    public function upload_by_user()
    {
        return $this->hasOne(User::class, 'id', 'upload_by');
    }

    protected $appends = [
        'total_subscriptions',
        'total_assigns',
        'total_active_subscriptions',
    ];

    public function getTotalAttribute()
    {
        return $this::count();
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'file_id');
    }

    public function getTotalSubscriptionsAttribute()
    {
        return $this::subscriptions()->count();
    }

    public function assigns()
    {
        return $this->hasMany(Assign::class, 'file_id');
    }

    public function getTotalAssignsAttribute()
    {
        return $this::assigns()->count();
    }

    public function getTotalActiveSubscriptionsAttribute()
    {
        return $this::subscriptions()->where('status', '<>', 0)->count();
    }
}
