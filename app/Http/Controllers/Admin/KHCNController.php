<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Log;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KHCNController extends Controller
{
    public function getKhcn(Request $request)
    {
        $phone = \App\Helpers\Utils::instance()->trimPhoneNumber($request->get('phone_number', ''));
        $code = $request->get('code');
        $data = $this::getApiKhcnWithCode($phone, $code);
        return response()->json(['data' => $data]);
    }

    public function logKhcn(Request $request)
    {
        $phone = \App\Helpers\Utils::instance()->trimPhoneNumber($request->get('phone_number', ''));
        $userId = auth()->user()->id;

        $log = new Log;
        $log->params = $phone;
        $log->user_id = $userId;
        $log->log_type_id = 1;
        $log->is_exist = Subscription::where([
            'phone_number' => $phone,
            'assigned_to' => $userId,
        ])->exists();
        $log->created_at = now();
        $log->save();

        return response()->json(['data' => true]);
    }

    public function getKhcnPackageObjects(Request $request)
    {
        $phone = \App\Helpers\Utils::instance()->trimPhoneNumber($request->get('phone_number', ''));
        $data = $this::getApiKhcnPackObjects($phone);
        return response()->json(['data' => $data]);
    }

    public function scanPackCode(Request $request)
    {
        $phones = $request->get('phones');

        $resData = [];

        foreach ($phones as $phone) {
            $p = \App\Helpers\Utils::instance()->trimPhoneNumber($phone['phoneNumber']);
            $r = [];
            $r['phoneNumber'] = $p;

            try {
                $data = $this::getApiKhcn($p);
                if ($data) {
                    $pckHistories = $data->pckHistories[0];

                    $r['code'] = $pckHistories->pckCode;
                    $r['type'] = $data->payType;
                    $r['balance'] = $data->unitsAvailable;
                    $r['startDate'] = Carbon::parse($pckHistories->startDate)->format('d/m/Y H:i:s');
                    $r['endDate'] = Carbon::parse($pckHistories->endDate)->format('d/m/Y H:i:s');
                    $r['status'] = 'Thành công';
                } else {
                    $r['status'] = 'Lỗi';
                }
            } catch (\Exception $e) {
                $r['status'] = 'Lỗi';
            }

            array_push($resData, $r);
        }

        return response()->json(['data' => $resData]);
    }

    public function searchKHCN(Request $request)
    {
        $phones = $request->get('phones');
        $code = $request->get('selectedPack');

        $resData = [];

        foreach ($phones as $phone) {
            $p = \App\Helpers\Utils::instance()->trimPhoneNumber($phone['phoneNumber']);
            $r = [];
            $r['phoneNumber'] = $p;

            try {
                $data = $this::getApiKhcn($p);
                if ($data) {
                    $packs = $data->pckHistories;
                    $r['type'] = $data->payType;
                    $r['balance'] = $data->unitsAvailable;
                    $r['code'] = $packs;
                    $r['status'] = 'Thành công';
                } else {
                    $r['status'] = 'Lỗi';
                }
            } catch (\Exception $e) {
                $r['status'] = 'Lỗi';
            }

            array_push($resData, $r);
        }

        return response()->json(['data' => $resData]);
    }

    public static function getApiKhcn($phone)
    {
        $username = 'test_khcn';
        $password = 'khcn@mbfkv2';

        $url = "https://hochiminh.mobifone.vn/sf/api/khcn/sub-detail?username=$username&password=$password&isdn=$phone";

        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            $url,
            [
                "verify" => false,
                "headers" => [
                    "Content-Type" => "application/json",
                ],
            ]
        );

        return json_decode($response->getBody());
    }

    public static function getApiKhcnWithCode($phone, $code)
    {
        $username = 'test_khcn';
        $password = 'khcn@mbfkv2';

        $url = "https://hochiminh.mobifone.vn/sf/api/khcn/sub-detail?username=$username&password=$password&isdn=$phone&code=$code";

        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            $url,
            [
                "verify" => false,
                "headers" => [
                    "Content-Type" => "application/json",
                ],
            ]
        );

        return json_decode($response->getBody());
    }

    public static function getApiKhcnPackObjects($phone)
    {
        $username = 'test_khcn';
        $password = 'khcn@mbfkv2';

        $url = "https://hochiminh.mobifone.vn/sf/api/khcn/program-obj?username=$username&password=$password&isdn=$phone&direction=I&fromDate=01/06/2023&code=";

        $client = new \GuzzleHttp\Client();
        $response = $client->request(
            'POST',
            $url,
            [
                "verify" => false,
                "headers" => [
                    "Content-Type" => "application/json",
                ],
            ]
        );

        return json_decode($response->getBody());
    }

    public function reportDetailKHCN(Request $request)
    {
        $fromDate = $request->get('from_date', null);
        $toDate = $request->get('to_date', null);
        $branchId = $request->get('branch_id', null);
        $userId = $request->get('user_id', null);
        $user = auth()->user();

        return response()->json([
            'status' => true,
            'data' => [],
        ]);
    }

    public function reportTotalKHCN(Request $request)
    {
        $fromDate = $request->get('from_date', null);
        $toDate = $request->get('to_date', null);
        $branchId = $request->get('branch_id', null);
        $userId = $request->get('user_id', null);

        $limit = $request->get('limit', 50);
        $page = $request->get('page', 1);
        $user = auth()->user();

        $model = new Log();
        $data = $model->scopeSearch($request)->paginate($limit, ['*'], 'page', $page);

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }
}
