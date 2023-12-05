<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\CtvUserRepositoryInterface;
use App\Interfaces\OtpRepositoryInterface;
use App\Models\Otp;
use App\Models\Pack;
use App\Models\RefundHistory;
use App\Models\SendOtpHistory;
use App\Models\UpgradeHistory;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OtpController extends Controller
{
    protected $otp;
    protected $ctv_user;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Repositories\OtpRepositoryInterface  $users
     * @return void
     */
    public function __construct(OtpRepositoryInterface $otp, CtvUserRepositoryInterface $ctv_user)
    {
        $this->otp = $otp;
        $this->ctv_user = $ctv_user;
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

    public function login(Request $request)
    {
        $email = $request->get('email', '');
        $user = $this->ctv_user->findBy([['email', $email]]);

        if (!$user) {
            throw new Exception('Not found !');
        }
        // Thực hiện login để lấy token
        $data = array(
            'username' => $user->email,
            'password' => $user->password,
        );
        $url = "https://congtacvien.mobifone.vn/aff-api/api/authenticate";
        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            $url,
            [
                "verify" => false,
                "headers" => [
                    "Content-Type" => "application/json",
                ],
                "json" => $data,
            ]
        );
        return response()->json(['data' => json_decode($response->getBody()), 'branch_id' => $user->branch_id], 200);
    }

    public function getOtp(Request $request)
    {
        $phone = $request->get('phone', '');
        $code = $request->get('code', '');
        $token = $request->get('token', '');
        $email = $request->get('email', '');

        $data = array(
            'msisdn' => $phone,
            'pckCode' => $code,
            'userName' => $email,
            'userName' => $email,
            'source' => 'API Tài Phát',
            'cusId' => '',
        );
        $url = "https://congtacvien.mobifone.vn/aff-api/api/transPackage/getOtp";
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
                "json" => $data,
            ]
        );

        return response()->json(['data' => json_decode($response->getBody())]);
    }

    public function confirmOtp(Request $request)
    {
        try {
            $phone = $request->get('phone', '');
            $code = $request->get('code', '');
            $token = $request->get('token', '');
            $otp = $request->get('otp', '');
            $email = $request->get('email', '');

            $otpHistory = new SendOtpHistory();
            $otpHistory->user_id = auth()->user()->id;
            $otpHistory->phone_number = $phone;
            $otpHistory->status = 0;
            $otpHistory->code = $code;
            $otpHistory->save();

            $data = array(
                'msisdn' => $phone,
                'pckCode' => $code,
                'userName' => $email,
                'otp' => $otp,
            );
            $url = "https://congtacvien.mobifone.vn/aff-api/api/transPackage/otpConfirm";
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
                    "json" => $data,
                ]
            );

            $res = json_decode($response->getBody());
            $res_data = $res->data;

            $resStatus = $res_data?->respStatus ?? 0;
            $otpHistory->status = $resStatus;
            $otpHistory->resp_content = $res_data?->respContent ?? '';
            $otpHistory->save();

            if ($resStatus === 1 || $resStatus === '1') {
                //Tìm mục hoàn tiền tương ứng để cập nhật kênh đăng ký
                RefundHistory::where([
                    "phone_number" => $phone,
                ])->update(['register_channel' => 'OTP']);

                UpgradeHistory::where([
                    "phone_number" => $phone,
                ])->update(['register_channel' => 'OTP']);
            }

            return response()->json(['data' => $res]);
        } catch (\Exception $e) {
            return $e;
        }
    }

    public function getTransactions(Request $request)
    {
        $id = $request->get('id', '');
        $token = $request->get('token', '');
        $phone_number = \App\Helpers\Utils::instance()->trimPhoneNumber($request->get('phone_number', ''));

        try {
            $data = $phone_number !== '' ? array(
                "msisdn" => $phone_number,
                "status" => 3,
                "isExport" => true,
                "page" => 1,
                "sizeOfPage" => 1000,
            ) : array(
                "startTime" => Carbon::now()->subDays(30)->format('d/m/Y'),
                "endTime" => Carbon::now()->format('d/m/Y'),
                "msisdn" => '',
                "status" => 3,
                "isExport" => true,
                "page" => 1,
                "sizeOfPage" => 1000,
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
                    "json" => $data,
                ]
            );

            $res = json_decode($response->getBody());
            $res_data = $res->data->items;

            $test = [];
            foreach ($res_data as $row) {
                //Tìm mục hoàn tiền tương ứng để cập nhật kênh đăng ký
                $refund = RefundHistory::where([
                    "phone_number" => $row->msisdn,
                ])->update(['register_channel' => 'OTP']);

                $up = UpgradeHistory::find($id);
                if ($up?->code) {
                    $pack = Pack::where('code', $up?->code)->first();

                    $up->status = 1;
                    $up->register_channel = 'OTP';
                    $up->err_amount = 0;
                    $up->res_amount = intval($pack->amount) - intval($up->amount);
                    $up->save();
                }
            }

            return response()->json(['data' => $res_data]);
        } catch (\Exception $e) {
            return $e;
        }
    }
}
