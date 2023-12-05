<?php

namespace App\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackCodeHistory extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = null;
    public $incrementing = false;

    protected $fillable = [
        "pack_code",
        "branch_id",
        "amount",
        "created_at",
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = new PackCodeHistory();

        if (isset($filters['pack_code'])) {
            $query = $query->where('pack_code', $filters['pack_code']);
        }

        if ($user->role <= config('constants.admin')) {
            if (isset($filters['branch_id'])) {
                $query = $query->where('branch_id', $filters['branch_id']);
            }

        } else {
            // Nếu là nhân viên thì lấy tất cả thông tin của nhân viên đó
            $query = $query->where('branch_id', $user->branch_id);
        }

        if (isset($filters['from_date']) && $filters['from_date'] != '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] != '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->with(['branch' => function ($query) {
            $query->select('*');
        }])
            ->orderBy('id', 'desc');
    }

    protected function calculateRevenue($amount, $pack_code, $type)
    {
        $revContants = [
            '6C90N-1' => 470000,
            '6C90N-2' => 490000,
            '12C90N-1' => 900000,
            '12C90N-2' => 950000,
        ];

        return $amount * $revContants["$pack_code-$type"];
    }

    public function getReportUserRevenueDetail($from_date, $to_date, $branch_id = null, $user_id = null, $pack_code = null)
    {
        $user = auth()->user();
        $data = PackCodeRequest::selectRaw('branches.display_name, users.id as user_id, users.name, phone_number, users.user_code, pack_code, pack_code_requests.type, pack_code_requests.created_at')
            ->join('users', 'users.id', '=', 'pack_code_requests.created_by')
            ->join('branches', 'branches.id', '=', 'users.branch_id')
            ->where('status', 2);

        if ($user->role === config('constants.manager')) {
            $data = $data->where('users.branch_id', $user->branch_id);
        }

        if ($user->role === config('constants.employee')) {
            $data = $data->where('users.id', $user->id);
        }

        if ($branch_id) {
            $data = $data->where('users.branch_id', $branch_id);
        }

        if ($user_id) {
            $data = $data->where('users.id', $user_id);
        }

        if ($from_date) {
            $data = $data->whereDate('pack_code_requests.created_at', '>=', $from_date);
        }

        if ($to_date) {
            $data = $data->whereDate('pack_code_requests.created_at', '<=', $to_date);
        }

        if ($pack_code) {
            $data = $data->where('pack_code', $pack_code);
        }

        $data = $data->orderBy('created_at')
            ->get()
            ->map(function ($item) {
                return [
                    'display_name' => $item->display_name,
                    'name' => $item->name,
                    'user_code' => $item->user_code,
                    'user_id' => $item->user_id,
                    'pack_code' => $item->pack_code,
                    'phone_number' => $item->phone_number,
                    'type' => $item->type,
                    'revenue' => $this->calculateRevenue(1, $item->pack_code, $item->type),
                    'created_at' => $item->created_at,
                ];
            });

        return $data;
    }
}
