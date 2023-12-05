<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScanBalance extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table = 'scan_balances';
    protected $fillable = [
        "phone_number",
        "balance",
        "current_balance",
        "user_id",
        "status",
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = $this;

        if ($user->role === config('constants.employee')) {
            $userId = $user->id;
            $query = $query->where('user_id', $userId);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('id', 'desc');
    }
}
