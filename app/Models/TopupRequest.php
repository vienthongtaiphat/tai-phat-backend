<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopupRequest extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "amount",
        "created_by",
        "status",
        "branch_id",
        "approved_by",
        "type",
        "created_at",
    ];

    public function requestUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = $this;

        if ($user->role <= config('constants.admin')) {
            // Nếu là admin thì lấy tất cả thông tin
        } else if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
            $query = $query->whereIn('created_by', $ids);
        } else {
            // Nếu là nhân viên thì lấy tất cả thông tin của nhân viên đó
            $query = $query->where('created_by', $user->id);
        }

        if (isset($filters['branch_id']) && $filters['branch_id']) {
            $query = $query->where('branch_id', $filters['branch_id']);
        }

        if (isset($filters['channel']) && $filters['channel']) {
            $query = $query->where('type', $filters['channel']);
        }

        if (isset($filters['user_id']) && $filters['user_id']) {
            $query = $query->where('approved_by', $filters['user_id']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] != '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] != '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with(['requestUser' => function ($query) {
            $query->select('name', 'id');
        }, 'approvedBy' => function ($query) {
            $query->select('name', 'id');
        },
            'branch' => function ($query) {
                $query->select('*');
            }])
            ->orderBy('created_at', 'desc');
    }
}
