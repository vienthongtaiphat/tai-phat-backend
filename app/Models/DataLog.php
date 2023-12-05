<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataLog extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "user_id",
        "phone_number",
        "created_at",
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function createLog($phoneNumber)
    {
        return $this->create([
            'user_id' => auth()->user()->id,
            "phone_number" => $phoneNumber,
        ]);
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = $this;

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

        return $query->with([
            'user' => function ($query) {
                $query->select('*');
            }])
            ->orderBy('id', 'desc');
    }

    public function scopeSearchTotal($filters = [])
    {
        $user = auth()->user();

        $query = $this;

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

    public function countByUser($userId, $filters = [])
    {
        $query = $this;

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        $query = $query->where('user_id', $userId);

        return $query->count();
    }
}