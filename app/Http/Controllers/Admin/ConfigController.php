<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function changeDiscount(Request $request)
    {
        $discount = $request->get('discount', 0);

        $config = Config::first();
        $config->discount = $discount;
        $config->save();

        return response()->json(['data' => $config], 200);
    }
}
