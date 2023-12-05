<?php

namespace App\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackCodeStore extends Model
{
    use HasFactory;

    protected $table = 'pack_stores';

    public $timestamps = true;

    protected $fillable = [
        "pack_code",
        "branch_id",
        "amount",
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = new PackCodeStore();

        if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $query = $query->where('branch_id', $user->branch_id);
        }

        return $query->with(['branch' => function ($query) {
            $query->select('*');
        }])
            ->orderBy('id', 'desc');
    }
}
