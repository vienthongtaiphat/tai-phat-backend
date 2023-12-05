<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\OtpRepositoryInterface;
use App\Models\Branch;
use App\Models\Config;
use App\Models\DichVu24hAccount;
use App\Models\Pack;
use App\Models\PackChangeHistory;
use App\Models\RefundHistory;
use App\Models\SendOtpHistory;
use App\Models\Subscription;
use App\Models\UpgradeHistory;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RefundController extends Controller
{
    protected $account;
    protected $user;
    protected $refund_history;
    protected $upgradeHistory;
    protected $pack;
    /**
     * Create a new controller instance.
     *
     * @param  \App\Repositories\OtpRepositoryInterface  $users
     * @return void
     */
    public function __construct(DichVu24hAccount $account, User $user, RefundHistory $refund_history, UpgradeHistory $upgradeHistory, Pack $pack)
    {
        $this->account = $account;
        $this->user = $user;
        $this->pack = $pack;
        $this->refund_history = $refund_history;
        $this->upgradeHistory = $upgradeHistory;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    protected function send24hRequest($refundAccount, $phoneNumber, $amount,
        $simtype, $channel, $code, $refcode) {
        $user = auth()->user();
        $telco = 'MOBI';
        $servgrou = '';
        $callurl = 'https://phplaravel-1100866-3856926.cloudwaysapps.com/api/refund/callback';
        $sign = md5($refundAccount->username . $telco . $simtype . $phoneNumber . $amount . $servgrou . $refcode . $callurl . $refundAccount->password);

        if (!$user) {
            throw new Exception('Not found');
        }

        $url = "https://dichvu24h.pro/api/v1/sendtran";
        $response = Http::asForm()->post($url, [
            'usercode' => $refundAccount->username,
            'telco' => $telco,
            'simtype' => $simtype,
            'mobile' => $phoneNumber,
            'amount' => $amount,
            'servgrou' => $servgrou,
            'timestop' => 240,
            'refcode' => $refcode,
            'callurl' => $callurl,
            'sign' => $sign,
        ])->throw()->json();

        return $response;
    }

    public function createRefund(Request $request)
    {
        try {
            $user = auth()->user();

            $refundAccount = $this->account->where('branch_id', $user->branch_id)->first();
            $phoneNumber = \App\Helpers\Utils::instance()->trimPhoneNumber($request->get('phone', ''));
            $amount = $request->get('amount', 0);
            $simtype = $request->get('simtype', 0);
            $channel = $request->get('channel', 2);
            $code = $request->get('code', '');
            $giftType = $request->get('gift_type', null);
            $registerChannel = Branch::find($user->branch_id)?->register_channel ?? '';
            $refcode = uniqid();

            $refundCount = $this->refund_history->where('phone_number', $phoneNumber)
                ->where('code', $code)
                ->where('status', 1)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30)->endOfDay())
                ->first();

            if ($refundCount) {
                return response()->json(['message' => 'Lỗi tạo trùng sđt trong 30 ngày'], 500);
            }

            $otp = SendOtpHistory::where('phone_number', $phoneNumber)
                ->where('code', $code)
                ->where('status', 1)
                ->whereDate('created_at', '>=', Carbon::now()->subDays(30)->endOfDay())
                ->first();

            if ($otp) {
                $registerChannel = 'OTP';
            }

            $pack = Pack::where('code', $code)->first();

            if ($channel === 1 && $giftType) {
                return response()->json(['message' => 'Lỗi không thể tặng sim cho kênh Mặc định'], 500);
            }

            $amount_tran = 0;
            if ($channel === 2 && $giftType === null) {
                $amount_tran = $amount;
            }

            $config = Config::first();
            $discount = intval(($config->discount / 100) * $amount_tran);
            $isExist = Subscription::where([
                'phone_number' => $phoneNumber,
                'assigned_to' => $user->id,
            ])->exists();

            $year = Carbon::now()->year;
            $month = Carbon::now()->month;

            $isDuplicate = $this->refund_history->where('phone_number', $phoneNumber)
                ->whereYear('created_at', '=', $year)
                ->whereMonth('created_at', '=', $month)
                ->exists();

            $response = $this->refund_history->create([
                'user_id' => $user->id,
                'phone_number' => $phoneNumber,
                'amount' => $amount,
                'amount_tran' => $amount_tran,
                'amount_discount' => $amount_tran - $discount,
                'refcode' => $refcode,
                'status' => null,
                'id_tran' => '',
                'account' => $refundAccount->id,
                'code' => $code,
                'channel' => $channel,
                'register_channel' => $registerChannel,
                'gift_type' => $giftType,
                'is_exist' => $isExist,
                'is_duplicate' => $isDuplicate,
                'simtype' => $simtype,
            ]);

            $branch = Branch::find($user->branch_id);

            if ($branch) {
                $channelName = $channel === 1 ? 'Mặc định' : 'EZ';
                $sType = $simtype === 1 ? 'Thuê bao trả sau' : 'Thuê bao trả trước';
                $message = "Hoàn tiền: " . $code .
                "\nSĐT: " . $phoneNumber .
                "\nNgười tạo: " . $user->name .
                    "\nKênh nạp: " . $channelName .
                    "\nLoại thuê bao: " . $sType .
                    "\nTỔNG SỐ TIỀN HOÀN: " . $amount;
                if ($branch?->refund_channel_id) {
                    \App\Helpers\SendTelegram::instance()->sendMessage($branch->refund_channel_id, $message);
                }
                if ($branch->id === 19) {
                    \App\Helpers\SendTelegram::instance()->sendMessage("-800825533", $message); // nhóm Phong
                }
            }

            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function updateRefund(Request $request)
    {
        try {
            $user = auth()->user();

            $id = trim($request->get('id', ''));
            $discountAmount = $request->get('amount_discount', 0);

            $refund = $this->refund_history->find($id);
            $refund->amount_discount = $discountAmount;
            $refund->save();
            return response()->json(['data' => $refund], 200);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function getBalance(Request $request)
    {
        $user = auth()->user();

        $refundAccount = $this->account->where('branch_id', $user?->branch_id ?? 0)->first();
        if (!$refundAccount || !$refundAccount->username || !$refundAccount->password) {
            throw new Exception('Not found');
        }
        $sign = md5($refundAccount->username . $refundAccount->password);

        if (!$user || $user->role === config('constants.employee')) {
            throw new Exception('Not found');
        }

        $url = "https://dichvu24h.pro/api/v1/getuseramou";
        $response = Http::asForm()->post($url, [
            'usercode' => $refundAccount->username,
            'sign' => $sign,
        ])->throw()->json();

        $data = [];
        if ($user->role === config('constants.manager')) {
            $accounts = $this->account->where('branch_id', $user->branch_id)->get();
        } else {
            $accounts = $this->account->where('branch_id', '<>', 0)->get();
        }
        foreach ($accounts as $key => $account) {
            $sign = md5($account->username . $account->password);

            $data[] = Http::asForm()->post($url, [
                'usercode' => $account->username,
                'sign' => $sign,
            ])->throw()->json();

            $data[$key]['username'] = $account->username;
        }

        return response()->json(['accounts' => $data], 200);
    }

    public function refundCallback(Request $request)
    {
        $idTran = $request->get('id_tran', '');
        $status = intval($request->get('status', 0));
        $refcode = $request->get('refcode', '');
        $amountTran = intval($request->get('amoutran', ''));
        $message = $request->get('message', '');

        $refundHistory = $this->refund_history->where('refcode', $refcode)
            ->orWhere('id_tran', $idTran)
            ->first();

        if (!$refundHistory) {
            throw new Exception('Not found !');
        }

        $config = Config::first();
        $discount = intval(($config->discount / 100) * $amountTran);

        $refundHistory->update([
            'status' => $status,
            'amount_tran' => $amountTran,
            'amount_discount' => $amountTran - $discount,
            'note' => $message,
        ]);

        $pack = $this->pack->where('code', $refundHistory->code)->first();

        $resAmount = bcsub($pack->amount, $amountTran);
        $upgradeHistory = $this->upgradeHistory->where('phone_number', $refundHistory->phone_number)
            ->Where('code', $refundHistory->code)
            ->orderBy('created_at', 'desc')
            ->first();

        $upgradeHistory->amount = $amountTran;
        $upgradeHistory->res_amount = $resAmount;
        $upgradeHistory->save();

        return response()->json(["status" => "1", "message" => "Đã nhận"], 200);
    }

    public function refreshTicket(Request $request)
    {
        $user = auth()->user();

        $refundAccount = $this->account->where('branch_id', $user->branch_id)->first();
        $refCode = $request->get('refcode', '');
        $idTran = $request->get('id_tran', 0);
        $sign = md5($refundAccount?->username . $refCode . $idTran . $refundAccount?->password);

        if (!$user || $user->role > 2) {
            throw new Exception('Not found !');
        }

        $url = "https://dichvu24h.pro/api/v1/gettran";
        $response = Http::asForm()->post($url, [
            'usercode' => $refundAccount?->username,
            'refcode' => $refCode,
            'id_tran' => $idTran,
            'sign' => $sign,
        ])->throw()->json();

        $refundHistory = $this->refund_history->where('refcode', $refCode)
            ->orWhere('id_tran', $idTran)
            ->first();

        if (!$refundHistory) {
            throw new Exception('Not found !');
        }

        $config = Config::first();
        $discount = intval(($config->discount / 100) * intval($response['amoutran']));

        $refundHistory->update([
            'status' => intval($response['status']),
            'amount_tran' => intval($response['amoutran']),
            'amount_discount' => intval($response['amoutran']) - $discount,
        ]);

        return response()->json(['data' => $response], 200);
    }

    public function approveRefund($id, $flag)
    {
        try {
            \DB::beginTransaction();
            $user = auth()->user();
            $isCreateUpgrade = true;
            $refundAccount = $this->account->where('branch_id', $user->branch_id)->first();

            $refundHistory = $this->refund_history->find($id);
            $amount = $refundHistory->amount;
            if (!$refundHistory) {
                throw new Exception('Not found !');
            }

            $pack = $this->pack->where('code', $refundHistory->code)->first();
            if ($refundHistory->channel === 2 || $flag) {
                if ($flag) {
                    $response = $refundHistory->update([
                        'amount_tran' => intval($amount),
                        'amount_discount' => intval($amount),
                        'status' => 2,
                        'note' => 'Tạo nâng cấp',
                    ]);
                } else {
                    $response = $refundHistory->update([
                        'status' => 2,
                        'note' => '',
                    ]);
                }

                //hoàn tiền EZ thì theo công thức cũ
                $resAmount = bcsub($amount, $pack->amount);
                $totalAmount = intval($amount);
            } else {
                $response = $this->send24hRequest($refundAccount, $refundHistory->phone_number, $refundHistory->amount,
                    0, $refundHistory->channel, $refundHistory->code, $refundHistory->refcode);

                if (intval($response['status']) > 90 && intval($response['status']) < 100) {
                    $isCreateUpgrade = false;
                }

                $response = $refundHistory->update([
                    'amount_tran' => isset($response['amoutran']) ? $response['amoutran'] : 0,
                    'status' => intval($response['status']),
                    'id_tran' => $response['id_tran'],
                ]);
                $totalAmount = isset($response['amoutran']) ? intval($response['amoutran']) : 0;
                $resAmount = bcsub($amount, $pack->amount);
            }

            //Tạo yêu cầu nâng cấp - đã duyệt

            $status = null; // Chờ duyệt
            if ($refundHistory->amount === 0 || $refundHistory->gift_type !== null) {
                $resAmount = 0;
            }

            if ($refundHistory->register_channel === 'OTP') {
                $status = 1; //Đã duyệt
            }

            $otp = SendOtpHistory::where(['phone_number' => $refundHistory->phone_number,
                'code' => $refundHistory->code])
                ->first();

            $registerChannel = $refundHistory->register_channel;
            // $createdAt = now();

            if ($otp) {
                $registerChannel = 'OTP';
                $refundHistory->update([
                    'register_channel' => $registerChannel,
                ]);
                // $createdAt = $otp->created_at;
            }

            $res_amount = intval($pack->amount) - intval($totalAmount);

            if ($isCreateUpgrade) {
                UpgradeHistory::create(
                    [
                        'phone_number' => $refundHistory->phone_number,
                        'code' => $refundHistory->code,
                        'amount' => $totalAmount,
                        'res_amount' => $res_amount,
                        'err_amount' => 0,
                        'pack_price' => $pack->pack_price,
                        'revenue' => $pack->price,
                        'real_revenue' => $pack->revenue,
                        'standard_amount' => $pack->amount,
                        'user_id' => $refundHistory->user_id,
                        'refund_id' => $refundHistory->id,
                        'channel' => $refundHistory->channel,
                        'status' => null,
                        'register_channel' => $registerChannel,
                        'updated_by' => $user->id,
                        'location_log' => 3,
                        "approved_user_id" => $user->id,
                        "approved_at" => now(),
                        // "created_at" => $createdAt,
                    ]);
            }
            \DB::commit();
            return $refundHistory;
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e;
        }
    }

    public function approveTicket(Request $request)
    {
        try {
            $id = $request->get('id', null);
            $flag = $request->get('flag', null);
            $refundHistory = $this->approveRefund($id, $flag);
            return response()->json(['data' => $refundHistory], 200);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getTotalAmount(Request $request)
    {
        $from_date = $request->get('from_date', null);
        $to_date = $request->get('to_date', null);

        if ($from_date && $to_date) {
            $amount = $this->refund_history->where('status', 2)
                ->whereDate('created_at', '>=', $from_date)
                ->whereDate('created_at', '<=', $to_date)
                ->sum('amount');

            $amount_tran = $this->refund_history->where('status', 2)
                ->whereDate('created_at', '>=', $from_date)
                ->whereDate('created_at', '<=', $to_date)
                ->sum('amount_tran');
        } else {
            $amount = $this->refund_history->where('status', 2)
                ->sum('amount');

            $amount_tran = $this->refund_history->where('status', 2)
                ->sum('amount_tran');
        }

        return response()->json(['amount' => $amount, 'amount_tran' => $amount_tran], 200);
    }

    public function delete($id)
    {
        try {
            $res = $this->refund_history->findOrFail($id);
            if ($res) {
                $res->delete();
            }
            return response()->json(['data' => true], 200);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function fallRequest($id)
    {
        $upgradeHistory = $this->upgradeHistory->where('id', $id)->first();

        if ($upgradeHistory) {
            $upgradeHistory->res_amount = 0;
            $upgradeHistory->err_amount = $upgradeHistory->amount;
            $upgradeHistory->status = 402;
            $upgradeHistory->save();
        }

        return response()->json(['data' => $upgradeHistory], 200);
    }

    public function unApproveRequest($id)
    {
        $upgradeHistory = $this->upgradeHistory->where('id', $id)->first();

        if ($upgradeHistory) {
            $upgradeHistory->status = 500;
            $upgradeHistory->save();
        }

        return response()->json(['data' => $upgradeHistory], 200);
    }

    public function syncUpgradeOSP($token, $branch_id)
    {
        try {
            \DB::beginTransaction();
            // Lấy danh sách nâng cấp
            $user = auth()->user();
            $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();
            $last30Days = now()->subDays(30)->endOfDay();

            if ($user->role < 3) {
                $upgradeHistory = $this->upgradeHistory
                    ->where('register_channel', 'OTP')
                    ->whereIn('user_id', $ids)
                    ->whereNull('status')
                    ->whereDate('created_at', '>=', $last30Days)
                    ->orderBy('created_at', 'desc')
                    ->distinct()
                    ->get();
            } else {
                throw new Exception('Bạn không có quyền đồng bộ !');
            }

            $successPhone = [];
            $failedPhone = [];

            foreach ($upgradeHistory as $upgrade) {
                $phone = $upgrade->phone_number;
                $code = $upgrade->code;

                $otp = SendOtpHistory::where([
                    'phone_number' => $phone,
                    'status' => 1,
                ])
                    ->whereIn('user_id', $ids)
                    ->whereDate('created_at', '>=', $last30Days)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $flag = false;

                if ($otp) {
                    $flag = true;
                    $created_at = Carbon::createFromFormat('d/m/Y', $otp->created_at)->toIso8601String();
                    $packCode = $otp->code;
                } else {
                    $params = array(
                        "msisdn" => $phone,
                        "status" => 3,
                        "isExport" => true,
                    );

                    $url = "https://congtacvien.mobifone.vn/aff-api/api/transPackage/get-trans";
                    $client = new \GuzzleHttp\Client();
                    $response = $client->request(
                        'POST',
                        $url,
                        [
                            "verify" => false,
                            "headers" => [
                                "Content-Type" => "application/json",
                                'Authorization' => "Bearer {$token}",
                            ],
                            "json" => $params,
                        ]
                    );

                    $resBody = json_decode($response->getBody());
                    $items = $resBody->data->items;

                    if (count($items)) {
                        $flag = true;
                        $packCode = $items[0]->packageName;
                        $revenue = intval($items[0]->packAmount);
                        $milliseconds = $items[0]->regTime;
                        $created_at = date("m/d/Y H:i:s", ($milliseconds / 1000));
                    }
                }

                if ($flag) {
                    $sumAmount = 0;

                    $lastestUpgrade = UpgradeHistory::where('phone_number', $phone)
                        ->whereNull('status')
                        ->whereIn('user_id', $ids)
                        ->where('code', $packCode)
                        ->whereDate('created_at', '>=', $last30Days)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (!$lastestUpgrade) {
                        $lastestUpgrade = UpgradeHistory::where('phone_number', $phone)
                            ->whereNull('status')
                            ->whereIn('user_id', $ids)
                            ->whereDate('created_at', '>=', $last30Days)
                            ->orderBy('created_at', 'desc')
                            ->first();
                    }
                    $sumAmount = UpgradeHistory::where('phone_number', $phone)
                        ->whereIn('user_id', $ids)
                        ->whereDate('created_at', '>', $last30Days)
                        ->whereNull('status')
                        ->sum('amount');

                    if ($lastestUpgrade) {
                        //Xóa tất cả nâng cấp cũ cùng chi nhánh
                        UpgradeHistory::where('phone_number', $phone)
                            ->whereNull('status')
                            ->whereIn('user_id', $ids)
                            ->whereDate('created_at', '>', $last30Days)
                            ->delete();

                        //Tất cả nâng cấp thuộc chi nhánh khác đều hoàn lỗi
                        $list = UpgradeHistory::where('phone_number', $phone)
                            ->whereNull('status')
                            ->whereNotIn('user_id', $ids)
                            ->whereDate('created_at', '>', $last30Days)
                            ->get();

                        foreach ($list as $item) {
                            $u = UpgradeHistory::find($item->id);
                            $u->res_amount = $u->amount;
                            $u->save();
                        }
                        //Add lại nâng cấp mới, amount = tổng hoàn
                        //res_amount = hoàn dư = tổng thực hoàn - hoàn cố định
                        $user_id = $lastestUpgrade?->user_id;
                        $channel = $lastestUpgrade?->channel;
                        $refund_id = $lastestUpgrade?->refund_id;

                        $res_amount = intval($lastestUpgrade?->standard_amount ?? 0) - intval($sumAmount);

                        $pack = PackChangeHistory::where('code', $packCode)
                            ->where('created_at', '<=', Carbon::parse($created_at))
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if (!$pack) {
                            $pack = Pack::where('code', $packCode)->first();
                        }
                        array_push($successPhone, $phone);
                        //Thêm nâng cấp mới
                        UpgradeHistory::create(
                            [
                                'phone_number' => $phone,
                                'code' => $packCode,
                                'amount' => $sumAmount,
                                'res_amount' => $res_amount,
                                'pack_price' => $pack->pack_price,
                                'revenue' => $pack->price,
                                'real_revenue' => $pack->revenue,
                                'standard_amount' => $pack->amount,
                                'err_amount' => 0,
                                'user_id' => $user_id,
                                'status' => 1,
                                'register_channel' => 'OTP',
                                'refund_id' => $refund_id,
                                'channel' => $channel,
                                'created_at' => $created_at,
                                'updated_by' => $user->id,
                                'approved_user_id' => $user->id,
                                'approved_at' => now(),
                                'location_log' => 4,
                            ]);
                    }
                }
            }

            \DB::commit();
            return response()->json(['data' => $successPhone, 'count' => count($successPhone)], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
