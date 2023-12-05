<?php

namespace App\Exports;

use App\Models\SendOtpHistory;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class OtpHistoryExport implements FromView
{
    public function view(): View
    {
        $model = new SendOtpHistory();
        $data = $model->scopeSearch()->get();
        return view('exports.otp_histories', [
            'otp_histories' => $data,
        ]);
    }
}