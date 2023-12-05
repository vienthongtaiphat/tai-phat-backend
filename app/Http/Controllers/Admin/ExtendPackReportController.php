<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExtendPack;
use App\Models\ExtendPackRequest;
use App\Models\User;
use Illuminate\Http\Request;

class ExtendPackReportController extends Controller
{
    public function getInputAdminReport(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);
        $pack_code = $request->get('pack_code', null);
        $user = auth()->user();

        $data = ExtendPackRequest::selectRaw('pack_code, sum(amount) as amount, DATE(created_at) as date')
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
        $user = auth()->user();

        $data = ExtendPackRequest::selectRaw('branches.display_name, branches.name, pack_code, revenue, real_revenue, phone_number, count(pack_code) as amount, DATE(extend_pack_requests.created_at) as date')
            ->leftJoin('users', 'users.id', '=', 'extend_pack_requests.created_by')
            ->leftJoin('branches', 'branches.id', '=', 'users.branch_id')
            ->where('status', 2);

        if ($user->role === config('constants.manager')) {
            $data = $data->where('users.branch_id', $user->branch_id);
        }

        if ($from_date) {
            $data = $data->whereDate('extend_pack_requests.created_at', '>=', $from_date);
        }

        if ($to_date) {
            $data = $data->whereDate('extend_pack_requests.created_at', '<=', $to_date);
        }

        if ($branch_id) {
            $data = $data->where('users.branch_id', $branch_id);
        }

        $data = $data->groupBy('date', 'pack_code', 'type', 'users.branch_id')
            ->get()
            ->map(function ($item) {
                $pack = ExtendPack::where('code', $item->pack_code)->first();
                return [
                    'name' => $item->name,
                    'display_name' => $item->display_name,
                    'phone_number' => $item->phone_number,
                    'user_code' => $item->user_code,
                    'pack_code' => $item->pack_code,
                    'date' => $item->date,
                    'total' => $item->amount,
                    'revenue' => $item->amount * $item->revenue,
                    'real_revenue' => $item?->real_revenue ?? 0,
                    'created_at' => $item->created_at,
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

        $data = ExtendPackRequest::selectRaw('sum(extend_pack_requests.revenue) as revenue, sum(extend_pack_requests.real_revenue) as real_revenue, branches.display_name, users.id as user_id, users.name, phone_number, users.user_code, pack_code, count(pack_code) as amount,extend_pack_requests.created_at ')
            ->join('users', 'users.id', '=', 'extend_pack_requests.created_by')
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
            $data = $data->whereDate('extend_pack_requests.created_at', '>=', $from_date);
        }

        if ($to_date) {
            $data = $data->whereDate('extend_pack_requests.created_at', '<=', $to_date);
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
                    'created_at' => $item->created_at,
                    'phone_number' => $item->phone_number,
                    'pack_code' => $item->pack_code,
                    'total' => $item->amount,
                    'pack_revenue' => $item->real_revenue,
                    'revenue' => $item->revenue,
                ];
            });

        return $data;
    }

    public function getReportUserRevenueDetail($from_date, $to_date, $branch_id = null, $user_id = null, $type = null, $pack_code = null)
    {
        $user = auth()->user();

        $data = ExtendPackRequest::selectRaw('extend_pack_requests.revenue, extend_pack_requests.real_revenue, branches.display_name, users.id as user_id, users.name, users.type, phone_number, users.user_code, pack_code, extend_pack_requests.created_at ')
            ->leftJoin('users', 'users.id', '=', 'extend_pack_requests.created_by')
            ->leftJoin('branches', 'branches.id', '=', 'users.branch_id')
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
            $data = $data->whereDate('extend_pack_requests.created_at', '>=', $from_date);
        }

        if ($to_date) {
            $data = $data->whereDate('extend_pack_requests.created_at', '<=', $to_date);
        }

        if ($pack_code) {
            $data = $data->where('pack_code', $pack_code);
        }

        $data = $data->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'display_name' => $item->display_name,
                    'name' => $item->name,
                    'user_code' => $item->user_code,
                    'user_id' => $item->user_id,
                    'created_at' => $item->created_at,
                    'phone_number' => $item->phone_number,
                    'pack_code' => $item->pack_code,
                    'pack_revenue' => $item->real_revenue,
                    'revenue' => $item?->revenue,
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
}
