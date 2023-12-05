<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\Pack;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "phone_number",
        "phone_type",
        "code",
        "register_by",
        "assigned_to",
        "register_date",
        "expired_date",
        "period",
        "upload_by",
        "file_id",
        "first_register_date",
        "first_expired_date",
        "profile",
        "status",
        "note",
        "user_note",
        "branch_id",
        "prior_user",
        "assigned_date",
    ];

    public function getTotalAttribute()
    {
        return $this::count();
    }

    public function fileInfo()
    {
        return $this->hasOne(Pack::class, 'id', 'file_id');
    }

    public function codeInfo()
    {
        return $this->hasOne(Pack::class, 'code', 'code');
    }

    public function upload_by_user()
    {
        return $this->hasOne(User::class, 'id', 'upload_by');
    }

    public function assigned_to_user()
    {
        return $this->hasOne(User::class, 'id', 'assigned_to');
    }

    public function register_by_user()
    {
        return $this->hasOne(User::class, 'id', 'register_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $data = new Subscription();
        if (isset($filters['phone_type']) && $filters['phone_type']) {
            $data = $data->where('phone_type', $filters['phone_type']);
        }

        if (isset($filters['register_date']) && strtotime($filters['register_date'])) {
            $data = $data->where('first_register_date', $filters['register_date']);
        }

        if (isset($filters['expired_date']) && strtotime($filters['expired_date'])) {
            $data = $data->where('expired_date', $filters['expired_date']);
        }

        if (isset($filters['code']) && $filters['code']) {
            $data = $data->where('code', $filters['code']);
        }

        if (isset($filters['assign_status']) && is_int($filters['assign_status']) && $filters['assign_status']) {
            $data = $data->where('assigned_to', $filters['assign_status'] === 1 ? '!=' : '=', null);
        }

        if (isset($filters['status']) && is_int($filters['status'])) {
            $data = $data->where('status', $filters['status']);
        }

        if (isset($filters['file_id']) && is_int($filters['file_id']) && $filters['file_id']) {
            $data = $data->where('file_id', $filters['file_id']);
        }

        if (isset($filters['khcn_code']) && $filters['khcn_code']) {
            $data = $data->where('note', 'like', '%' . $filters['khcn_code'] . '%');
        }

        if ($user->role === config('constants.employee')) {
            $userId = $user->id;
            $data = $data->where(function ($query) use ($userId) {
                $query->orWhere('assigned_to', '=', $userId)
                    ->orWhere('register_by', '=', $userId);
            });
        } elseif ($user->role === config('constants.manager')) {
            $data = $data->where('branch_id', '=', $user->branch_id);
        }

        return $data->with(['assigned_to_user' => function ($query) {
            $query->select('id', 'name');
        },
            'branch' => function ($query) {
                $query->select('*');
            }])
            ->orderBy('assigned_date', 'desc')
            ->orderBy('created_at', 'desc');
    }

    public static function validateDuplicate($subscriptions)
    {
        $data = [];
        foreach ($subscriptions as $subscription) {
            $phoneNumber = \App\Helpers\Utils::instance()->trimPhoneNumber($subscription['phone_number']);
            $isExist = Subscription::where('phone_number', $phoneNumber)
                ->where('code', $subscription['code'])
                ->exists();
            if ($isExist) {
                array_push($data, $phoneNumber);
            }
        }

        return $data;
    }
}
