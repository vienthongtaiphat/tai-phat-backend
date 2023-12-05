<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UserExport implements FromView
{
    private $request;

    public function __construct($request)
    {
        $this->request = $request;
    }

    public function view(): View
    {
        $model = new User();
        $data = $model->scopeSearch($this->request)->get();
        return view('exports.users', [
            'otp_histories' => $data,
        ]);
    }
}
