<?php

namespace App\Exports;

use App\Http\Controllers\Admin\ReportController;
use App\Models\DichVu24hAccount;
use App\Models\RefundHistory;
use App\Models\UpgradeHistory;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class TotalRevenueExport implements FromView
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $from_date = $this->request['from_date'];
        $to_date = $this->request['to_date'];
        $branch_id = $this->request['branch_id'] ?? null;
        $user_id = $this->request['user_id'] ?? null;
        $type = $this->request['type'] ?? null;
        $register_channel = $this->request['register_channel'] ?? null;

        $model = new ReportController(new DichVu24hAccount, new User, new RefundHistory, new UpgradeHistory);
        $data = $model->getTotalRevenue($from_date,
            $to_date,
            $branch_id,
            $user_id,
            $type,
            $register_channel);

        usort($data, function ($a, $b) {return $b['revenue'] > $a['revenue'];});
        return view('exports.total_revenue', [
            'upgrade_histories' => $data,
        ]);
    }
}
