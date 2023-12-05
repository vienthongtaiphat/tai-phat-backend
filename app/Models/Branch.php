<?php

namespace App\Models;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $fillable = [
        'name',
        'display_name',
        'channel_id',
        'refund_channel_id',
        'balance_channel_id',
    ];

    protected $appends = [
        'total_subscriptions',
        'total_members',
    ];

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'branch_id');
    }

    public function members()
    {
        return $this->hasMany(User::class, 'branch_id');
    }

    public function getTotalSubscriptionsAttribute()
    {
        return $this::subscriptions()->count();
    }

    public function getTotalMembersAttribute()
    {
        return $this::members()->count();
    }
}
