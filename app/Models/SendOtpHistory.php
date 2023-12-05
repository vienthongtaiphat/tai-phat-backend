<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SendOtpHistory extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "phone_number",
        "user_id",
        "code",
        "status",
        "resp_content",
    ];

    public function getTotalAttribute()
    {
        return $this::count();
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('d/m/Y');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();
        $query = new SendOtpHistory();
        if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $ids);
        } elseif ($user->role === config('constants.employee')) {
            //Nếu là nhân viên thì lấy thông tin của chính mình
            $query = $query->where('user_id', $user->id);
        }

        return $query->with(['user' => function ($query) {
            $query->select('id', 'name');
        }]);
    }
}
