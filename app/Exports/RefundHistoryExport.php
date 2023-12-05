<?php

namespace App\Exports;

use App\Models\RefundHistory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class RefundHistoryExport implements FromView
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $model = new RefundHistory();
        $data = $model->scopeSearch($this->request)->orderBy('created_at', 'desc')->get();
        return view('exports.refund_histories', [
            'refund_histories' => $data,
        ]);
    }
}
