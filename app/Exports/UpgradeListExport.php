<?php

namespace App\Exports;

use App\Models\UpgradeHistory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UpgradeListExport implements FromView
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $model = new UpgradeHistory();
        $data = $model->scopeSearch($this->request)->get();
        return view('exports.upgrade_histories', [
            'upgrade_histories' => $data,
        ]);
    }
}
