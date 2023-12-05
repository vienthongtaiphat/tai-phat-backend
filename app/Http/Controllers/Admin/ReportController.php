<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\OtpRepositoryInterface;
use App\Models\DichVu24hAccount;
use App\Models\Otp;
use App\Models\PackCodeHistory;
use App\Models\PackCodeRequest;
use App\Models\PayDebtRequest;
use App\Models\RefundHistory;
use App\Models\UpgradeHistory;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    protected $account;
    protected $user;
    protected $refund_history;
    protected $upgradeHistory;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Repositories\OtpRepositoryInterface  $users
     * @return void
     */
    public function __construct(DichVu24hAccount $account, User $user, RefundHistory $refund_history, UpgradeHistory $upgradeHistory)
    {
        $this->account = $account;
        $this->user = $user;
        $this->refund_history = $refund_history;
        $this->upgradeHistory = $upgradeHistory;
    }

    public function getReportRefund(Request $request)
    {
        $fromDate = $request->get('from_date', null);
        $toDate = $request->get('to_date', null);
        $status = $request->get('status', null);

        $query = $this->refund_history->scopeSearch([
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'status' => $status,
        ]);

        $refundHistories = $query->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data' => $refundHistories,
        ]);
    }

    public function getKHCNLogs(Request $request)
    {
        $data = DB::select("SELECT user_id, users.name, branches.display_name, count(user_id) as count FROM logs, users, branches WHERE user_id = users.id AND branches.id = users.branch_id AND log_type_id = 1 and MONTH(logs.created_at) = MONTH(CURRENT_DATE()) AND YEAR(logs.created_at) = YEAR(CURRENT_DATE()) GROUP BY user_id, users.name, branches.display_name ORDER BY count DESC");
        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function getRequestLogs(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);

        $data = PackCodeHistory::select('branches.display_name', 'branches.id', 'pack_code', DB::raw('SUM(pack_code_histories.amount) AS sum'))
            ->leftJoin('branches', 'pack_code_histories.branch_id', '=', 'branches.id')
            ->where('amount', '>', 0)
            ->where('branches.id', "<>", null)
            ->whereDate('pack_code_histories.created_at', '>=', $from_date)
            ->whereDate('pack_code_histories.created_at', '<=', $to_date)
            ->groupBy('branch_id', 'pack_code')
            ->get();

        foreach ($data as $key => $branch) {
            //Tính tổng yêu cầu chuyển code theo chi nhánh
            $ids = User::where('branch_id', $branch->id)->pluck('id')->toArray();
            $count = PackCodeRequest::whereIn('created_by', $ids)
                ->where('status', 2)
                ->where('pack_code', $branch->pack_code)
                ->whereDate('created_at', '>=', $from_date)
                ->whereDate('created_at', '<=', $to_date)
                ->count();

            $data[$key]->count = $count;
        }

        //Tính tồn cho kho admin
        $countOf6C90N = PackCodeHistory::where('branch_id', 0)
            ->where('pack_code', '6C90N')
            ->where('amount', '>', 0)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount');

        $countOf12C90N = PackCodeHistory::where('branch_id', 0)
            ->where('pack_code', '12C90N')
            ->where('amount', '>', 0)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount');

        $countOutOf6C90N = PackCodeHistory::where('branch_id', '<>', 0)
            ->where('pack_code', '6C90N')
            ->where('amount', '>', 0)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount');

        $countOfOut12C90N = PackCodeHistory::where('branch_id', '<>', 0)
            ->where('pack_code', '12C90N')
            ->where('amount', '>', 0)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount');

        return response()->json([
            'status' => true,
            'data' => $data,
            'admin' => [
                'countIn6C90N' => $countOf6C90N,
                'countIn12C90N' => $countOf12C90N,
                'countOut6C90N' => $countOutOf6C90N,
                'countOut12C90N' => $countOfOut12C90N,
            ],
        ]);
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

    public function getInputAdminReport(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);
        $pack_code = $request->get('pack_code', null);
        $user = auth()->user();

        $data = PackCodeHistory::selectRaw('pack_code, sum(amount) as amount, DATE(created_at) as date')
        // ->where('amount', '>', 0)
            ->where('branch_id', 0);

        if ($from_date) {
            $data = $data->whereDate('created_at', '>=', $from_date);
        }

        if ($to_date) {
            $data = $data->whereDate('created_at', '<=', $to_date);
        }

        if ($pack_code) {
            $data = $data->where('pack_code', $pack_code);
        }

        $data = $data->groupBy('date', 'pack_code')
            ->get()
            ->map(function ($item) {
                return [
                    'pack_code' => $item->pack_code,
                    'date' => $item->date,
                    'total' => $item->amount,
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function getRequestReportRevenue(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);
        $branch_id = $request->get('branch_id', null);
        $pack_code = $request->get('pack_code', null);
        $user = auth()->user();

        $data = PackCodeRequest::selectRaw('branches.display_name, pack_code, pack_code_requests.type, count(pack_code) as amount, DATE(pack_code_requests.created_at) as date')
            ->leftJoin('users', 'users.id', '=', 'pack_code_requests.created_by')
            ->leftJoin('branches', 'branches.id', '=', 'users.branch_id')
            ->where('status', 2);

        if ($user->role === config('constants.manager')) {
            $data = $data->where('users.branch_id', $user->branch_id);
        }

        if ($from_date) {
            $data = $data->whereDate('pack_code_requests.created_at', '>=', $from_date);
        }

        if ($to_date) {
            $data = $data->whereDate('pack_code_requests.created_at', '<=', $to_date);
        }

        if ($branch_id) {
            $data = $data->where('users.branch_id', $branch_id);
        }

        if ($pack_code) {
            $data = $data->where('pack_code', $pack_code);
        }

        $data = $data->groupBy('date', 'pack_code', 'type', 'users.branch_id')
            ->get()
            ->map(function ($item) {
                return [
                    'display_name' => $item->display_name,
                    'pack_code' => $item->pack_code,
                    'type' => $item->type,
                    'date' => $item->date,
                    'total' => $item->amount,
                    'revenue' => $this->calculateRevenue($item->amount, $item->pack_code, $item->type),
                ];
            });

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function getReportUserRevenue($from_date, $to_date, $branch_id, $user_id, $pack_code)
    {
        $user = auth()->user();
        $data = PackCodeRequest::selectRaw('branches.display_name, users.id as user_id, users.name, phone_number, users.user_code, pack_code, pack_code_requests.type, count(pack_code) as amount,pack_code_requests.created_at')
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

        $data = $data->groupBy('created_by', 'pack_code', 'type')
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
                    'total' => $item->amount,
                    'revenue' => $this->calculateRevenue($item->amount, $item->pack_code, $item->type),
                    'created_at' => $item->created_at,
                ];
            });

        return $data;
    }

    public function getReportUserRevenueDetail($from_date, $to_date, $branch_id = null, $user_id = null, $type = null, $pack_code = null)
    {
        $user = auth()->user();
        $data = PackCodeRequest::selectRaw('branches.display_name, users.id as user_id, users.name, phone_number, users.user_code, users.type, pack_code, pack_code_requests.type, pack_code_requests.created_at')
            ->join('users', 'users.id', '=', 'pack_code_requests.created_by')
            ->join('branches', 'branches.id', '=', 'users.branch_id')
            ->where('status', 2);

        if ($user?->role === config('constants.manager')) {
            $data = $data->where('users.branch_id', $user->branch_id);
        }

        // if ($user->role === config('constants.employee')) {
        //     $data = $data->where('users.id', $user->id);
        // }

        if ($branch_id) {
            $data = $data->where('users.branch_id', $branch_id);
        }

        if ($user_id) {
            $data = $data->where('users.id', $user_id);
        }

        if ($type) {
            $data = $data->where('users.type', $type);
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

    public function getRequestReportUserRevenue(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);
        $branch_id = $request->get('branch_id', null);
        $user_id = $request->get('user_id', null);
        $pack_code = $request->get('pack_code', null);
        $report_type = $request->get('report_type', null);

        $data = $report_type === 'detail' ? $this->getReportUserRevenueDetail($from_date, $to_date, $branch_id, $user_id, $pack_code) : $this->getReportUserRevenue($from_date, $to_date, $branch_id, $user_id, $pack_code);

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    private function getPayDebtRevenue(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);

        $data = PayDebtRequest::select('branches.display_name', DB::raw('SUM(pay_debt_requests.amount) AS sum'))
            ->join('branches', 'pay_debt_requests.branch_id', '=', 'branches.id')
            ->where('branch_id', '>', 0)
            ->where('status', 2)
            ->whereDate('pay_debt_requests.created_at', '>=', $from_date)
            ->whereDate('pay_debt_requests.created_at', '<=', $to_date)
            ->groupBy('branch_id')->get();

        return $data;
    }

    public function getPayDebtReport(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);
        $branch_id = $request->get('branch_id', null);
        $user = auth()->user();

        $data = PackCodeRequest::selectRaw('branches.display_name, pack_code, pack_code_requests.type, count(pack_code) as amount, DATE(pack_code_requests.created_at) as date')
            ->join('users', 'users.id', '=', 'pack_code_requests.approved_by')
            ->join('branches', 'branches.id', '=', 'users.branch_id')
            ->where('status', 2);

        if ($user->role === config('constants.manager')) {
            $data = $data->where('users.branch_id', $user->branch_id);
        }

        if ($from_date) {
            $data = $data->whereDate('pack_code_requests.created_at', '>=', $from_date);
        }

        if ($to_date) {
            $data = $data->whereDate('pack_code_requests.created_at', '<=', $to_date);
        }

        if ($branch_id) {
            $data = $data->where('branches.id', $branch_id);
        }

        $data = $data->groupBy('date', 'pack_code', 'type', 'users.branch_id')
            ->get()
            ->map(function ($item) {
                return [
                    'display_name' => $item->display_name,
                    'pack_code' => $item->pack_code,
                    'type' => $item->type,
                    'date' => $item->date,
                    'total' => $item->amount,
                    'revenue' => $this->calculateRevenue($item->amount, $item->pack_code, $item->type),
                ];
            });

        $payDebtRevenue = $this->getPayDebtRevenue($request);
        return response()->json([
            'status' => true,
            'data' => $data,
            'pay_debt_revenue' => $payDebtRevenue,
        ]);
    }

    public function reportUpgrade(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);
        $branch_id = $request->get('branch_id', null);
        $user_id = $request->get('user_id', null);
        $register_channel = $request->get('register_channel', null);

        $data = $this->upgradeHistory->getReport([
            'from_date' => $from_date,
            'to_date' => $to_date,
            'branch_id' => $branch_id,
            'user_id' => $user_id,
            'register_channel' => $register_channel,
        ]);

        return response()->json([
            'data' => $data,
        ]);
    }

    public function getTotalRevenue($from_date,
        $to_date,
        $branch_id,
        $user_id,
        $type,
        $register_channel) {
        $merged = [];

        $data = [];
        $extendData = [];
        $sendCodeData = [];
        $upgradeChannels = array("KH23", "KH27", "KH29", "GA27", "OTP");

        if (!$register_channel || $register_channel === "KH" || $register_channel === "OTP") {
            $upgradeHistory = new UpgradeHistory;
            $data = $upgradeHistory->getReport([
                'from_date' => $from_date,
                'to_date' => $to_date,
                'branch_id' => $branch_id,
                'user_id' => $user_id,
                'type' => $type,
                'register_channel' => $register_channel,
            ]);

            foreach ($data as $item) {
                if ($item?->user?->id) {
                    if ($item->status === 402 || $item->status === 500 || $item->err_amount > 0) {
                        $revenue = 0;
                        $packRevenue = 0;
                        $packPrice = 0;
                        $amountTran = 0;
                        $resAmount = 0;
                    } else {
                        $price = $item?->revenue ?? 0;
                        $standard_amount = $item?->standard_amount ?? 0;
                        $packRevenue = $item?->real_revenue ?? 0;
                        $revenue = $price - $standard_amount;
                        $packPrice = $item->pack_price ?? 0;
                        $amountTran = $item->amount;
                        $resAmount = $item->res_amount;
                    }

                    $newItem = [
                        'revenue' => $revenue,
                        'user_code' => $item?->user?->user_code ?? '',
                        'user_id' => $item?->user?->id ?? '',
                        'display_name' => $item?->user?->branch?->display_name ?? '',
                        'name' => $item?->user?->name ?? '',
                        'type' => $item?->user?->type ?? '',
                        'phone_number' => $item->phone_number,
                        'created_at' => $item->created_at,
                        'code' => $item->code,
                        'pack_revenue' => $packRevenue,
                        'register_channel' => $item->register_channel,
                        'gift_type' => $item->gift_type,
                        'amount_tran' => $amountTran,
                        'res_amount' => $resAmount,
                        'err_amount' => $item->err_amount,
                        'pack_price' => $packPrice,
                        'status' => $item->status,
                    ];

                    array_push($merged, $newItem);
                }
            }
        }

        if (!$register_channel || $register_channel === 'GH') {
            $extendController = new ExtendPackReportController();
            $extendData = $extendController->getReportUserRevenueDetail($from_date, $to_date, $branch_id, $user_id, $type, null);

            foreach ($extendData as $item) {
                $newItem = [
                    'revenue' => $item['revenue'],
                    'display_name' => $item['display_name'],
                    'user_id' => $item['user_id'],
                    'user_code' => isset($item['user_code']) ? $item['user_code'] : '',
                    'name' => $item['name'],
                    'phone_number' => $item['phone_number'],
                    'created_at' => $item['created_at'],
                    'code' => $item['pack_code'],
                    'register_channel' => 'GH',
                    'gift_type' => null,
                    'amount_tran' => 0,
                    'res_amount' => 0,
                    'err_amount' => 0,
                    'pack_revenue' => $item['pack_revenue'],
                    'pack_price' => 0,
                ];

                array_push($merged, $newItem);
            }
        }

        if (!$register_channel || $register_channel === 'CODE' || $register_channel === 'CODE') {
            $sendCodeData = $this->getReportUserRevenueDetail($from_date, $to_date, $branch_id, $user_id, $type, null);
            foreach ($sendCodeData as $item) {
                $newItem = [
                    'revenue' => $item['revenue'],
                    'user_code' => isset($item['user_code']) ? $item['user_code'] : '',
                    'user_id' => $item['user_id'],
                    'display_name' => $item['display_name'],
                    'name' => $item['name'],
                    'phone_number' => $item['phone_number'],
                    'created_at' => $item['created_at'],
                    'code' => $item['pack_code'],
                    'register_channel' => 'CODE',
                    'gift_type' => null,
                    'amount_tran' => 0,
                    'res_amount' => 0,
                    'err_amount' => 0,
                    'pack_revenue' => 0,
                    'pack_price' => 0,
                ];

                array_push($merged, $newItem);
            }
        }

        return $merged;
    }

    public function reportTotalRevenue(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);
        $branch_id = $request->get('branch_id', null);
        $user_id = $request->get('user_id', null);
        $type = $request->get('type', null);
        $register_channel = $request->get('register_channel', null);

        $merged = $this->getTotalRevenue($from_date,
            $to_date,
            $branch_id,
            $user_id,
            $type,
            $register_channel);

        return response()->json([
            'data' => $merged,
        ]);
    }
}
