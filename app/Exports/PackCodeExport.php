<?php

namespace App\Exports;

use App\Models\PackCodeHistory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PackCodeExport implements FromView
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $model = new PackCodeHistory();
        $from_date = $this->request['from_date'];
        $to_date = $this->request['to_date'];
        $branch_id = isset($this->request['branch_id']) ? $this->request['branch_id'] : null;
        $user_id = isset($this->request['user_id']) ? $this->request['user_id'] : null;
        $pack_code = isset($this->request['pack_code']) ? $this->request['pack_code'] : null;

        $data = $model->getReportUserRevenueDetail($from_date, $to_date, $branch_id, $user_id, $pack_code);
        return view('exports.pack_code', [
            'list' => $data,
        ]);
    }
}
