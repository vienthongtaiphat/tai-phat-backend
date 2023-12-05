<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\RefundHistory;
use App\Models\TopupMonthRevenue;
use App\Models\TopupRequest;
use App\Models\User;
use Illuminate\Http\Request;

class TopupReportController extends Controller
{
    public function getReport($from_date, $to_date, $branch)
    {
        $ids = User::where('branch_id', $branch->id)->pluck('id')->toArray();

        $inTotal1 = TopupRequest::where('branch_id', $branch->id)
            ->where('status', 2)
            ->where('type', 1)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount');

        $outTotal1 = RefundHistory::whereIn('user_id', $ids)
            ->where('status', 2)
            ->where('channel', 1)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount_discount');

        $data1 = [
            'type' => 1,
            'inTotal' => $inTotal1,
            'outTotal' => $outTotal1,
        ];

        $inTotal2 = TopupRequest::where('branch_id', $branch->id)
            ->where('status', 2)
            ->where('type', 2)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount');

        $outTotal2 = RefundHistory::whereIn('user_id', $ids)
            ->where('status', 2)
            ->where('channel', 2)
            ->whereDate('created_at', '>=', $from_date)
            ->whereDate('created_at', '<=', $to_date)
            ->sum('amount_tran');

        $data2 = [
            'type' => 2,
            'inTotal' => $inTotal2,
            'outTotal' => $outTotal2,
        ];

        $month = intval(date('m', strtotime($to_date))) - 1;

        $lastRevenue = TopupMonthRevenue::where([
            'branch_id' => $branch->id,
            'month' => $month])
            ->first();

        return [
            'name' => $branch->display_name,
            'id' => $branch->id,
            'data' => [$data1, $data2],
            'lastRevenue' => $lastRevenue,
            'month' => $month,
        ];

    }

    public function getTopupReport(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);

        $branches = Branch::all();
        $listData = [];
        foreach ($branches as $branch) {
            $result = $this->getReport($from_date, $to_date, $branch);
            array_push($listData, $result);
        }

        return response()->json([
            'status' => true,
            'data' => $listData,
            'branches' => $branches,
        ]);
    }

}