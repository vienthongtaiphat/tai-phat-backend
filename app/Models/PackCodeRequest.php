<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackCodeRequest extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "pack_code",
        "type",
        "phone_number",
        "created_by",
        "status",
        "approved_by",
    ];

    public function requestUser()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = new PackCodeRequest();

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

        return $query->with(['requestUser' => function ($query) {
            $query->select('name', 'id');
        }, 'approvedBy' => function ($query) {
            $query->select('name', 'id');
        }])
            ->orderBy('id', 'desc');
    }
}
