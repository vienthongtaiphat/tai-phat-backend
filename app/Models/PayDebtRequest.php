<?php

namespace App\Models;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayDebtRequest extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "amount",
        "created_by",
        "status",
        "branch_id",
        "approved_by",
        "created_at",
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'created_by');
    }

    public function requestUser()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function approved()
    {
        return $this->hasOne(User::class, 'id', 'approved_by');
    }

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = new PayDebtRequest();

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

        if (isset($filters['from_date']) && $filters['from_date'] != '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] != '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with('branch',
            'user', 'approved')
            ->orderBy('id', 'desc');
    }
}
