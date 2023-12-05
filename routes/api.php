<?php

use App\Exports\DataExport;
use App\Exports\ExtendExport;
use App\Exports\OtpHistoryExport;
use App\Exports\PackCodeExport;
use App\Exports\RefundHistoryExport;
use App\Exports\TotalRevenueExport;
use App\Exports\UpgradeHistoryExport;
use App\Exports\UserExport;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\ExtendPackReportController;
use App\Http\Controllers\Admin\KHCNController;
use App\Http\Controllers\Admin\OtpController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\TopupReportController;
use App\Http\Controllers\AuthenticateController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\TelegramController;
use App\Models\Branch;
use App\Models\BranchTarget;
use App\Models\CallHistory;
use App\Models\Config;
use App\Models\DataLog;
use App\Models\ExtendPackRequest;
use App\Models\FileUploadedHistory;
use App\Models\Log;
use App\Models\Pack;
use App\Models\PackChangeHistory;
use App\Models\PayDebtRequest;
use App\Models\RefundHistory;
use App\Models\ScanBalance;
use App\Models\Subscription;
use App\Models\TopupMonthRevenue;
use App\Models\UpgradeHistory;
use App\Models\User;
use App\Models\UserRevenue;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

//https://phplaravel-1100866-3856926.cloudwaysapps.com/api/call-history 0 0 * * *
//https://phplaravel-1100866-3856926.cloudwaysapps.com/api/get-users-revenue 0 0 * * *
//https://phplaravel-1100866-3856926.cloudwaysapps.com/api/job-topup-month-revenue 0 0 * * *
//https://phplaravel-1100866-3856926.cloudwaysapps.com/api/auto-logout 0 0 * * *
//https://phplaravel-1100866-3856926.cloudwaysapps.com/api/remove-expired-subscriptions
//job xoá toàn bộ scan balances sau 8h30 sáng
//https://phplaravel-1100923-3857334.cloudwaysapps.com/api/clear-scan-balance-list 0 0 * * *
Route::get('/clear-scan-balance-list', function (Request $request) {
    $h = date('H');
    if (intval($h) === 8) {
        DB::statement("SET foreign_key_checks=0");
        DB::table('scan_balances')->truncate();
        DB::statement("SET foreign_key_checks=1");

        return 'OK';
    }
    return 'Fail';
});

//job quét số dư
//https://phplaravel-1100923-3857334.cloudwaysapps.com/api/scan-balance 0 0 * * *
Route::get('/scan-balance', function (Request $request) {
    $list = ScanBalance::where('status', '<>', 1)->get();
    foreach ($list as $l) {
        try {
            $scan = ScanBalance::find($l->id);
            $data = KHCNController::getApiKhcn($scan->phone_number);
            if ($data) {
                $currentBalance = intval(str_replace(",", "", $data->unitsAvailable)) ?? 0;
                $scan->current_balance = $currentBalance;

                if ($scan->balance <= $currentBalance) {
                    $scan->status = 1;
                    $user = User::find($scan->user_id);
                    $branch = Branch::find($user->branch_id);
                    if ($branch) {
                        $message = "SĐT: " . $scan->phone_number .
                        "\nNgười tạo: " . $user->name .
                            "\nSố dư: " . $currentBalance;

                        if ($branch?->refund_channel_id) {
                            \App\Helpers\SendTelegram::instance()->sendMessage($branch->refund_channel_id, $message);
                        }
                    }
                }
                $scan->save();
            }
        } catch (\Exception $e) {

        }
    }

    return 'ok';
});

//job logout after 12h
Route::get('/auto-logout', function (Request $request) {
    $h = date('H');
    if (intval($h) === 23) {
        $users = User::where('activated', 1)->get();
        $res = [];
        foreach ($users as $user) {
            $last_token = $user->remember_token;
            $user->remember_token = null;
            $user->save();
            if ($last_token) {
                try {
                    $t = new \PHPOpenSourceSaver\JWTAuth\Token($last_token);
                    \JWTAuth::manager()->invalidate($t, $forceForever = false);
                    array_push($res, $user->id);
                } catch (\Exception $e) {}
            }
        }

        return $res;
    }

    return 'Fail';
});
Route::post('/khcn', [KHCNController::class, 'getKhcn']);
Route::post('/log-khcn', [KHCNController::class, 'logKhcn']);
Route::post('/khcn/pack-objects', [KHCNController::class, 'getKhcnPackageObjects']);
Route::get('/update-pack-history', function (Request $request) {
    $packs = Pack::whereNotNull('pack_price')->get();

    foreach ($packs as $pack) {
        $p = PackChangeHistory::where('code', $pack->code)
            ->orderBy('created_at', 'desc')
            ->first();

        $p->pack_price = $pack->pack_price;
        $p->save();
    }

    return 'OK';
});
Route::get('/job-topup-month-revenue', function (Request $request) {

    $now = Carbon::now();

    $day = $now->day;
    $month = $now->month - 1;
    $year = $now->year;

    if ($month === 0) {
        $month = 12;
        $year = $now->year - 1;
    }
    // Kiểm tra nếu ngày hiện tại là 1 thì mới tính
    if (intval($day) !== 1) {
        return null;
    }
    $myDate = "$month/01/$year";
    $date = Carbon::createFromFormat('m/d/Y', $myDate);

    $startDate = $date->firstOfMonth()->format('Y-m-d');
    $endDate = $date->endOfMonth()->format('Y-m-d');
    $branches = Branch::all();

    //Xoá hết data cũ của tháng
    TopupMonthRevenue::where([
        'month' => $month,
        'year' => $year,
    ])->delete();

    foreach ($branches as $branch) {
        $controller = new TopupReportController();
        $report = $controller->getReport($startDate, $endDate, $branch);
        $report = $report['data'];

        $lastRevenue = TopupMonthRevenue::where('branch_id', $branch->id)->orderBy('created_at', 'desc')->first();
        $query = new TopupMonthRevenue();
        $query->month = $month;
        $query->year = $year;
        $query->branch_id = $branch->id;
        $query->i24h_balance = (intval($report[0]['inTotal']) - intval($report[0]['outTotal'])) + $lastRevenue->i24h_balance;
        $query->ez_balance = (intval($report[1]['inTotal']) - intval($report[1]['outTotal'])) + $lastRevenue->ez_balance;
        $query->save();
    }

    return 'OK';
});

Route::get('/trim-subscription', function (Request $request) {
    $count = 0;
    $list = Subscription::whereRaw('LENGTH(phone_number) > 10')->get();
    foreach ($list as $u) {
        try {
            $upgrade = Subscription::find($u->id);
            $upgrade->phone_number = trim($upgrade->phone_number);
            $upgrade->save();
            $count++;
        } catch (\Exception $e) {}
    }
    return $count;
});

Route::get('/add-data-log', function (Request $request) {
    Subscription::whereNotNull('assigned_to')->orderBy('id')->chunk(10000, function ($logs) {
        foreach ($logs as $log) {
            $l = new DataLog();
            $l->user_id = $log->assigned_to;
            $l->phone_number = $log->phone_number;
            $l->created_at = $log->created_at;
            $l->save();
        }
    });

    return 'Thành công';
});

Route::get('/remove-expired-subscriptions', function (Request $request) {
    $last60Days = now()->subDays(60)->endOfDay();
    Subscription::whereDate('created_at', '<', $last60Days)->delete();
    FileUploadedHistory::whereDate('created_at', '<', $last60Days)->delete();
    Log::whereDate('created_at', '<', $last60Days)->delete();
    DataLog::whereDate('created_at', '<', $last60Days)->delete();
    return 'Thành công';
});

Route::get('/log-exists', function (Request $request) {
    Log::orderBy('id')->chunk(10000, function ($logs) {
        foreach ($logs as $log) {
            $l = Log::find($log->id);
            $l->is_exist = Subscription::where([
                'phone_number' => $log->params,
                'assigned_to' => $log->user_id,
            ])->exists();
            $l->save();
        }
    });

    return 'Thành công';
});

Route::get('/refund-exists', function (Request $request) {
    RefundHistory::orderBy('id')->chunk(10000, function ($logs) {
        foreach ($logs as $log) {
            $l = RefundHistory::find($log->id);
            $l->is_exist = Subscription::where([
                'phone_number' => $log->phone_number,
                'assigned_to' => $log->user_id,
            ])->exists();
            $l->save();
        }
    });

    return 'Thành công';
});

Route::get('/refund-duplicate', function (Request $request) {
    RefundHistory::orderBy('id')->chunk(10000, function ($logs) {
        foreach ($logs as $log) {
            $date = Carbon::createFromFormat('Y-m-d H:i:s', $log->created_at);
            $year = $date->year;
            $month = $date->month;

            $l = RefundHistory::find($log->id);
            $l->is_duplicate = RefundHistory::where('phone_number', $log->phone_number)
                ->where('id', '<>', $log->id)
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->exists();
            $l->save();
        }
    });

    return 'Thành công';
});

Route::get('/update_pack_price', function (Request $request) {
    UpgradeHistory::orderBy('id')->chunk(10000, function ($logs) {
        foreach ($logs as $log) {
            $upgrade = UpgradeHistory::find($log->id);
            $pack = PackChangeHistory::where('code', $log->code)
                ->where('created_at', '<=', Carbon::parse($upgrade->created_at))
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$pack) {
                $pack = Pack::where('code', $log->code)->first();
            }

            $upgrade->pack_price = $pack?->pack_price ?? 0;
            $upgrade->save();
        }
    });

    return 'Thành công';
});

//Trường hợp dư khoảng trắng
Route::get('/trim-log-1', function (Request $request) {
    $count = 0;
    $list = Log::whereRaw('LENGTH(params) > 10')->get();
    foreach ($list as $u) {
        try {
            $upgrade = Log::find($u->id);
            $upgrade->params = trim($upgrade->params);
            $upgrade->save();
            $count++;
        } catch (\Exception $e) {}
    }
    return $count;
});

//Trường hợp thiếu số 0
Route::get('/trim-log-2', function (Request $request) {
    $count = 0;
    $list = Log::whereRaw('LENGTH(params) < 10')->get();
    foreach ($list as $u) {
        try {
            $upgrade = Log::find($u->id);
            $upgrade->params = "0" . $upgrade->params;
            $upgrade->save();
            $count++;
        } catch (\Exception $e) {}
    }
    return $count;
});

Route::get('/check-linecall', function (Request $request) {
    $listUsers = User::groupBy('line_call')->pluck('line_call')->toArray();
    $callHistories = CallHistory::groupBy('accountcode')->pluck('accountcode')->toArray();

    $array3 = array_diff($callHistories, $listUsers);
    return ['listUsers' => $listUsers, 'callHistories' => $callHistories, 'array_diff' => $array3];
});

Route::get('/update-upgrade-revenue', function (Request $request) {
    $list = ExtendPackRequest::get();
    $count = 0;
    foreach ($list as $u) {
        $upgrade = ExtendPackRequest::find($u->id);
        $upgrade->phone_number = trim($upgrade->phone_number);
        $upgrade->save();
        $count++;
    }
    return $count;
});

Route::get('/update-amount', function (Request $request) {
    $list = UpgradeHistory::whereDate('created_at', '>=', now()->subDays(3)->startOfDay())->get();
    $count = 0;
    foreach ($list as $u) {
        $upgrade = UpgradeHistory::find($u->id);
        $refund = RefundHistory::find($u->refund_id);

        if ($refund) {
            $upgrade->amount = $refund->amount_tran;
            $upgrade->res_amount = intval($u->standard_amount ?? 0) - intval($refund->amount_tran ?? 0);
            $upgrade->save();
            $count++;
        }
    }
    return $count;
});

Route::get('/update-res-amount', function (Request $request) {
    $list = UpgradeHistory::whereDate('created_at', '>=', now()->subDays(1)->startOfDay())->get();
    $count = 0;
    foreach ($list as $u) {
        $upgrade = UpgradeHistory::find($u->id);

        $res_amount = intval($upgrade->standard_amount ?? 0) - intval($upgrade->amount ?? 0);
        $upgrade->res_amount = $res_amount;
        $upgrade->res_amount = $res_amount;
        $upgrade->save();
        $count++;
    }
    return $count;
});

Route::get('/update-upgrade', function (Request $request) {
    $list = UpgradeHistory::whereDate('created_at', '>=', now()->subDays(40)->startOfDay())->get();
    $count = 0;
    foreach ($list as $u) {
        $upgrade = UpgradeHistory::find($u->id);
        // $pack = PackChangeHistory::where('code', $u->code)
        //     ->where('created_at', '<=', Carbon::parse($upgrade->created_at))
        //     ->orderBy('created_at', 'desc')
        //     ->first();

        // if (!$pack) {
        //     $pack = Pack::where('code', $u->code)->first();
        // }
        $pack = Pack::where('code', $u->code)->first();
        $upgrade->pack_price = $pack?->pack_price ?? 0;
        $upgrade->real_revenue = $pack->revenue ?? 0;
        $upgrade->save();
        $count++;
    }
    return $count;
});

Route::get('/all-code', function (Request $request) {
    $packs = Pack::select('code', 'duration', 'amount', 'price')->get();
    return $packs;
});

Route::get('/chiet-khau', function (Request $request) {
    $packs = Config::first();
    return $packs;
});

Route::get('/call-history', function (Request $request) {
    try {
        $startDate = Carbon::now()->subDays(1)->startOfDay()->timestamp;
        $endDate = Carbon::now()->timestamp;
        $secretKey = 'e2dec29fa8c11d26b6d999a455c627ce';
        $url = "https://apps.worldfone.vn/externalcrm/getcdrs.php?secret=$secretKey&&startdate=$startDate&&enddate=$endDate&&pageSize=100";

        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'GET',
            $url,
            [
                "verify" => false,
                "headers" => [
                    "Content-Type" => "application/json",
                ],
            ]
        );

        $res = json_decode($response->getBody());
        $res_data = $res;
        $maxPage = intval($res_data->max_page) + 1;
        $currentPage = intval($res_data->cur_page);
        // return $res;
        for ($i = $currentPage; $i < $maxPage; ++$i) {
            $url = "https://apps.worldfone.vn/externalcrm/getcdrs.php?secret=$secretKey&&startdate=$startDate&&enddate=$endDate&&pageSize=1000&&page=$i";

            $client = new \GuzzleHttp\Client();
            $response = $client->request(
                'GET',
                $url,
                [
                    "verify" => false,
                    "headers" => [
                        "Content-Type" => "application/json",
                    ],
                ]
            );

            $res = json_decode($response->getBody());
            $res_data = $res->data;

            foreach ($res_data as $row) {
                if (CallHistory::where('uniqueid', $row->uniqueid)->doesntExist()) {
                    CallHistory::create([
                        "created_at" => $row->calldate,
                        "src" => $row->src,
                        "dst" => $row->dst,
                        "disposition" => match ($row->disposition) {
                            'ANSWERED' => 1,
                            'NO ANSWER' => 2,
                            'BUSY' => 3,
                            default => 4
                        },
                        "duration" => $row->duration,
                        "billsec" => $row->billsec,
                        "holdtime" => $row->holdtime,
                        "waitingtime" => $row->waitingtime,
                        "uniqueid" => $row->uniqueid,
                        "accountcode" => $row->accountcode,
                        "did_number" => $row->did_number,
                        "carrier" => 1,
                        "direction" => match ($row->direction) {
                            'inbound' => 1,
                            default => 2
                        },
                    ]);
                }
            }
        }
        return response()->json(['data' => true]);
    } catch (\Exception $e) {
        return $e;
    }
});

Route::get('/get-users-revenue', function (Request $request) {
    // Truncate the table.
    $data = UserRevenue::getUserRevenue();
    return $data;
});

Route::get('/telegram', [TelegramController::class, 'updatedActivity']);
Route::post('/sim-push-telegram', [TelegramController::class, 'simPushTelegram']);
Route::post('/login', [AuthenticateController::class, 'authenticate']);
Route::post('/refund/callback', [RefundController::class, 'refundCallback']);
Route::post('/upload-banner', function (Request $request) {
    $path = Storage::disk('public')->putFileAs(
        'images', $request->file('file'), 'banner.jpg'
    );
    $response = array(
        'status' => 'success',
    );
    return $response;
});

Route::middleware(['api', 'auth'])->group(function () {
    Route::get('/all-branches', function (Request $request) {
        $packs = Branch::select('id', 'name')->get();
        return $packs;
    });

    Route::get('/get-top-revenue', function (Request $request) {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        $data = UserRevenue::select('user_id', 'user_code', 'name', DB::raw('sum(revenue) as revenue'))
            ->leftJoin('users', 'users.id', '=', 'month_revenues.user_id')
            ->where('month', $month)
            ->where('year', $year)
            ->groupBy('user_id')
            ->orderBy('revenue', 'desc')
            ->take(10)
            ->get();

        return $data;
    });

    Route::get('/get-chart-data/{month}/{year}', function ($month, $year) {
        $user = auth()->user();

        if ($user->role >= config('constants.manager')) {
            $branches = [$user->branch_id];
        } else {
            $branches = Branch::pluck('id')->toArray();
        }
        foreach ($branches as $branch_id) {
            $data['CN' . $branch_id] = UserRevenue::select('day', 'month', DB::raw('sum(revenue) as revenue'))
                ->leftJoin('users', 'users.id', '=', 'month_revenues.user_id')
                ->where('users.branch_id', $branch_id)
                ->where('month', $month)
                ->where('year', $year)
                ->groupBy('day', 'month')
                ->get();

            $targets['CN' . $branch_id] = BranchTarget::select('target')
                ->where('branch_id', $branch_id)
                ->where('month', $month)
                ->where('year', $year)
                ->first();
        }

        return response()->json(['data' => $data, 'target' => $targets]);
    });

    Route::get('/user', function (Request $request) {
        $user = auth()->user();
        if (!$user->activated) {
            return response()->json([
            ], 401);
        }

        return $user;
    });

    Route::get('/users', function (Request $request) {
        $user = auth()->user();

        if ($user->role < config('constants.manager')) {
            $users = User::select('id', 'name')->get();
        } else {
            $branch_id = $user->branch_id;
            $users = User::select('id', 'name')->where('branch_id', $branch_id)->get();
        }
        return $users;
    });

    Route::group(['prefix' => 'otp'], function () {
        Route::post('/login', [OtpController::class, 'login']);
        Route::post('/get-otp', [OtpController::class, 'getOtp']);
        Route::post('/confirm-otp', [OtpController::class, 'confirmOtp']);
        Route::post('/transactions', [OtpController::class, 'getTransactions']);
    });

    Route::group(['prefix' => 'config'], function () {
        Route::post('/discount', [ConfigController::class, 'changeDiscount']);
    });

    Route::group(['prefix' => 'export'], function () {
        Route::post('/otp_history', function (Request $request) {
            return Excel::download(new OtpHistoryExport($request->all()), 'Lịch sử OTP.xlsx');
        });

        Route::post('/refund_history', function (Request $request) {
            return Excel::download(new RefundHistoryExport($request->all()), 'Lịch sử hoàn tiền.xlsx');
        });

        Route::post('/subscriptions', function (Request $request) {
            return Excel::download(new DataExport($request->all()), 'Data.xlsx');
        });

        Route::post('/users', function (Request $request) {
            return Excel::download(new UserExport($request->all()), 'Users.xlsx');
        });

        Route::post('/upgrade-report', function (Request $request) {
            return Excel::download(new TotalRevenueExport($request->all()), 'TotalRevenueExport.xlsx');
        });

        Route::post('/upgrades', function (Request $request) {
            return Excel::download(new UpgradeHistoryExport($request->all()), 'UpgradeHistoryExport.xlsx');
        });

        Route::post('/pack-code-report', function (Request $request) {
            return Excel::download(new PackCodeExport($request->all()), 'PackCodeExport.xlsx');
        });

        Route::post('/extend-report', function (Request $request) {
            return Excel::download(new ExtendExport($request->all()), 'ExtendExport.xlsx');
        });
    });

    Route::group(['prefix' => 'khcn'], function () {
        // Route::post('/', [KHCNController::class, 'getKhcn']);

        Route::post('/report', [KHCNController::class, 'reportDetailKHCN']);
        Route::post('/report-total', [KHCNController::class, 'reportTotalKHCN']);
    });

    Route::post('/scan-pack-code', [KHCNController::class, 'scanPackCode']);
    Route::post('/search-khcn', [KHCNController::class, 'searchKHCN']);

    Route::group(['prefix' => 'report'], function () {
        Route::post('/th', [ReportController::class, 'getReportRefund']);
        Route::get('/log-rank-khcn', [ReportController::class, 'getKHCNLogs']);
        Route::post('/upgrade', [ReportController::class, 'reportUpgrade']);
        Route::post('/total-revenue', [ReportController::class, 'reportTotalRevenue']);
    });

    Route::group(['prefix' => 'refund'], function () {
        Route::post('/total-amount', [RefundController::class, 'getTotalAmount']);
        Route::post('/create', [RefundController::class, 'createRefund']);
        Route::post('/update', [RefundController::class, 'updateRefund']);
        Route::get('/balance', [RefundController::class, 'getBalance']);
        Route::delete('/delete/{id}', [RefundController::class, 'delete']);
        Route::post('/fall-request/{id}', [RefundController::class, 'fallRequest']);
        Route::post('/unapprove-request/{id}', [RefundController::class, 'unApproveRequest']);
        Route::post('/refresh', [RefundController::class, 'refreshTicket']);
        Route::post('/approve', [RefundController::class, 'approveTicket']);
        Route::get('/sync-upgrade-osp/{token}/{branch_id}', [RefundController::class, 'syncUpgradeOSP']);
    });

    Route::group(['prefix' => 'pack-request'], function () {
        Route::post('/sum-pack-request', [ReportController::class, 'getRequestLogs']);
        Route::post('/report-revenue', [ReportController::class, 'getRequestReportRevenue']);
        Route::post('/report-admin-input', [ReportController::class, 'getInputAdminReport']);
        Route::post('/report-user-revenue', [ReportController::class, 'getRequestReportUserRevenue']);
        Route::post('/report-pay-debt', [ReportController::class, 'getPayDebtReport']);
    });

    Route::group(['prefix' => 'extend-pack'], function () {
        Route::post('/report-revenue', [ExtendPackReportController::class, 'getRequestReportRevenue']);
        Route::post('/report-admin-input', [ExtendPackReportController::class, 'getInputAdminReport']);
        Route::post('/report-user-revenue', [ExtendPackReportController::class, 'getRequestReportUserRevenue']);
        Route::post('/report-pay-debt', [ExtendPackReportController::class, 'getPayDebtReport']);
    });

    Route::group(['prefix' => 'topup'], function () {
        Route::post('/report', [TopupReportController::class, 'getTopupReport']);
    });
});

Route::get('/cron-khcn', function (Request $request) {
    $list = Subscription::where('note', null)->take(200)->orderBy('id', 'desc')->get();
    foreach ($list as $s) {
        $data = KHCNController::getApiKhcn($s->phone_number, '');

        if (isset($data->unitsAvailable) && count($data->packageObjects)) {
            $sub = Subscription::find($s->id);
            $sub->balance = $data->unitsAvailable;
            $sub->note = implode(",", array_unique(array_column($data->packageObjects, 'code')));
            $sub->save();
        }
    }

    return 'OK';
});

Route::get('/test', function (Request $request) {

    return PayDebtRequest::with('requestUser', 'approvedBy')->orderBy('created_at', 'desc')->first();

});

Route::get('/job-add-refund', function (Request $request) {
    $nullUpgrades = UpgradeHistory::where('refund_id', null)->get();

    $error = [];
    foreach ($nullUpgrades as $upgrade) {
        $phone = $upgrade->phone_number;
        $code = $upgrade->code;

        $refund = RefundHistory::where([
            'phone_number' => $phone,
            'code' => $code,
        ])
            ->orderBy('created_at', 'desc')
            ->first();

        if ($refund) {
            UpgradeHistory::where('id', $upgrade->id)
                ->update([
                    'refund_id' => $refund->id,
                    'channel' => $refund->channel,
                    'register_channel' => $refund->register_channel,
                    'gift_type' => $refund->gift_type,
                ]);
        } else {
            array_push($error, $phone);
        }
    }

    return $error;
});

Route::get('logs', function () {
    $files = Storage::disk('logs')->files('');

    return $files;
});

Route::post('log', [LogController::class, 'getLogDetail']);
Route::post('delete-log', [LogController::class, 'removeLog']);
