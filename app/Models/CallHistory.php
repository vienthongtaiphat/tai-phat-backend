<?php

namespace App\Models;

use App\Models\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallHistory extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "src",
        "dst",
        "disposition", //1: ANSWERED, 2: NO ANSWER, 3: BUSY, 4: FAILED
        "duration",
        "billsec",
        "holdtime",
        "waitingtime",
        "uniqueid",
        "accountcode",
        "did_number",
        "carrier", //1: mobifone
        "direction", //1: inbound, 2 outbound
        "created_at",
        "updated_at",
    ];

    public function userCall()
    {
        return $this->hasOne(User::class, 'line_call', 'accountcode');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = CallHistory::selectRaw('call_histories.created_at, branches.display_name as branch_name, users.name as user_name, accountcode, count(*) as count, sum(duration) as totalDuration, sum(waitingtime) as totalWaitingTime, disposition')
            ->rightJoin('users', 'users.line_call', 'call_histories.accountcode')
            ->rightJoin('branches', 'branches.id', 'users.branch_id');

        if ($user->role === config('constants.manager')) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $user->branch_id)->pluck('line_call')->toArray();
            $query = $query->whereIn('accountcode', $ids);
        } else if ($user->role === config('constants.employee')) {
            // Nếu là nhân viên thì lấy tất cả thông tin của nhân viên đó
            $query = $query->where('accountcode', $user->line_call);
        }

        if (isset($filters['branch_id']) && $filters['branch_id']) {
            // Nếu là quản lý thì lấy tất cả thông tin của nhân viên cùng chi nhánh
            $ids = User::where('branch_id', $filters['branch_id'])->pluck('line_call')->toArray();
            $query = $query->whereIn('accountcode', $ids);
        }

        if (isset($filters['from_date']) && $filters['from_date'] != '') {
            $query = $query->whereDate('call_histories.created_at', '>=', $filters['from_date']);
        } else {
            $query = $query->whereDate('call_histories.created_at', Carbon::today());
        }

        if (isset($filters['to_date']) && $filters['to_date'] != '') {
            $query = $query->whereDate('call_histories.created_at', '<=', $filters['to_date']);
        } else {
            $query = $query->whereDate('call_histories.created_at', Carbon::today());
        }

        return $query
            ->groupBy(DB::raw('Date(call_histories.created_at)'), 'accountcode')
            ->orderBy('call_histories.created_at', 'desc')
            ->get()
            ->toArray();
    }
}
