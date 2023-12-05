<?php

namespace App\Exports;

use App\Models\Subscription;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class DataExport implements FromView
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $model = new Subscription();
        $data = $model->scopeSearch($this->request)->get();
        return view('exports.data', [
            'data_list' => $data,
        ]);
    }
}