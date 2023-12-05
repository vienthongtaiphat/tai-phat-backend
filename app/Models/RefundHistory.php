<?php

namespace App\Models;

use App\Models\DichVu24hAccount;
use App\Models\Pack;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RefundHistory extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "phone_number",
        "note",
        "amount",
        "amount_tran",
        "amount_discount",
        "code",
        "account",
        "user_id",
        "status",
        "id_tran",
        "is_exist",
        "refcode",
        "channel",
        "register_channel",
        "gift_type",
        "created_at",
        "is_duplicate",
        "simtype",
    ];

    protected $appends = [
        'total_amount',
    ];

    public function getTotalAmountAttribute()
    {
        return $this::where('status', 2)->sum('amount');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function refundAccount()
    {
        return $this->hasOne(DichVu24hAccount::class, 'id', 'account');
    }

    public function pack()
    {
        return $this->hasOne(Pack::class, 'code', 'code');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();
        set_time_limit(0);
        $query = new RefundHistory();
        if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $ids);
        } elseif ($user->role === config('constants.employee')) {
            //Nếu là nhân viên thì lấy thông tin của chính mình
            $query = $query->where('user_id', $user->id);
        }

        if (isset($filters['user_id'])) {
            $query = $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['is_exist'])) {
            $query = $query->where('is_exist', $filters['is_exist']);
        }

        if (isset($filters['phone_number']) && $filters['phone_number'] !== '') {
            $query = $query->where('phone_number', \App\Helpers\Utils::instance()->trimPhoneNumber($filters['phone_number']));
        }

        if (isset($filters['code']) && $filters['code'] !== '') {
            $query = $query->where('code', $filters['code']);
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $query = $query->where('status', $filters['status']);
        }

        if (isset($filters['account']) && $filters['account'] !== '') {
            $query = $query->where('account', $filters['account']);
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with(['user' => function ($query) {
            $query->select('id', 'name', 'branch_id', 'user_code');
        },
            'refundAccount' => function ($query) {
                $query->select('id', 'username');
            }]);
    }
}
