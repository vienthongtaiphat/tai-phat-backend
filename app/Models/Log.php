<?php

namespace App\Models;

use App\Models\LogType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "params",
        "log_type_id",
        "user_id",
        "is_exist",
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function logType()
    {
        return $this->hasOne(LogType::class, 'id', 'log_type_id');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = new Log();

        if ($user->role === config('constants.employee')) {
            //Nếu là nhân viên thì lấy thông tin của chính mình
            $query = $query->where('user_id', $user->id);
        } else if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $ids);
        }

        if (isset($filters['user_id'])) {
            $query = $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['is_exist'])) {
            $query = $query->where('is_exist', $filters['is_exist']);
        }

        if (isset($filters['branch_id'])) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $filters['branch_id'])->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $ids);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with(['logType' => function ($query) {
            $query->select('*');
        },
            'user' => function ($query) {
                $query->select('*');
            }])
            ->orderBy('id', 'desc');
    }

    public function scopeSearchTotal($filters = [])
    {
        $user = auth()->user();

        $query = new Log();

        if ($user->role === config('constants.employee')) {
            //Nếu là nhân viên thì lấy thông tin của chính mình
            $query = $query->where('user_id', $user->id);
        } else if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $ids);
        }

        if (isset($filters['user_id'])) {
            $query = $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['is_exist'])) {
            $query = $query->where('is_exist', $filters['is_exist']);
        }

        if (isset($filters['branch_id'])) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $filters['branch_id'])->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $ids);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with(['user' => function ($query) {
            $query->select('*');
        }])
            ->groupBy('user_id')
            ->orderBy('id', 'desc');
    }

    public function countByUser($userId, $isExist, $filters = [])
    {
        $query = new Log();

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $query = $query->where('is_exist', $isExist);
        $query = $query->where('user_id', $userId);

        return $query->count();
    }
}
