<?php

namespace App\Models;

use App\Models\Pack;
use App\Models\RefundHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpgradeHistory extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable = [
        "phone_number",
        "note",
        "amount",
        "res_amount",
        "err_amount",
        "standard_amount",
        "pack_price",
        "revenue",
        "real_revenue",
        "code",
        "user_id",
        "updated_by",
        "status",
        "location_log",
        "gift_type",
        "channel",
        "refund_id",
        "register_channel",
        "created_at",
        "approved_user_id",
        "approved_at",
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function pack()
    {
        return $this->hasOne(Pack::class, 'code', 'code');
    }

    public function refund()
    {
        return $this->hasOne(RefundHistory::class, 'id', 'refund_id');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();
        set_time_limit(0);
        $query = new UpgradeHistory();
        if ($user?->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
            $query = $query->whereIn('user_id', $ids);
        } elseif ($user?->role === config('constants.employee')) {
            //Nếu là nhân viên thì lấy thông tin của chính mình
            $query = $query->where('user_id', $user->id);
        }

        if (isset($filters['phone_number']) && $filters['phone_number'] !== '') {
            $query = $query->where('phone_number', 'like', '%' . $filters['phone_number'] . '%');
        }

        if (isset($filters['code']) && $filters['code'] !== '') {
            $query = $query->where('code', 'like', '%' . $filters['code'] . '%');
        }

        if (isset($filters['status']) && $filters['status'] === 0) {
            $query = $query->whereNull('status');
        } else if (isset($filters['status']) && $filters['status'] === 1) {
            $query = $query->whereNotNull('status');
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with(['user' => function ($query) {
            $query->select('id', 'name');
        }])->orderBy('created_at', 'desc');
    }

    public function getReport($filters = [], $all = false)
    {
        $user = auth()->user();
        set_time_limit(0);
        $query = new UpgradeHistory();
        $type = $filters['type'] ?? null;

        if ($filters['branch_id'] !== 999) {
            if ($user?->role === config('constants.manager') || $user?->role === config('constants.employee')) {
                // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
                $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
                $query = $query->whereIn('user_id', $ids);
            }

            if (isset($filters['branch_id']) && $filters['branch_id'] !== '') {
                $ids = User::where('branch_id', $filters['branch_id'])->pluck('id')->toArray();
                $query = $query->whereIn('user_id', $ids);
            }
        }

        if (!$all) {
            $query = $query->whereNotNull('status');
        }

        if (isset($filters['user_id']) && $filters['user_id'] !== '') {
            $query = $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['register_channel'])) {
            if ($filters['register_channel'] === 'OTP') {
                $query = $query->where('register_channel', 'OTP');
            } else {
                $query = $query->where('register_channel', 'like', 'KH%');
            }
        }

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with(['user' => function ($query) use ($type) {
            if ($type) {
                $query = $query->select('*')->where('type', $type);
            } else {
                $query->select('*');
            }

        },
            'refund' => function ($query) {
                $query->select('id', 'channel', 'register_channel', 'amount_tran', 'gift_type');
            },
            'pack' => function ($query) {
                $query->select('code', 'price', 'amount', 'revenue');
            },
            'User.Branch' => function ($query) {
                $query->select('id', 'display_name');
            }])
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
