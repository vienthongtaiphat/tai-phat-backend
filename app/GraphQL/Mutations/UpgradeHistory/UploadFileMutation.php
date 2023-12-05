<?php
namespace App\GraphQL\Mutations\UpgradeHistory;

use App\Models\Branch;
use App\Models\Pack;
use App\Models\PackChangeHistory;
use App\Models\RefundHistory;
use App\Models\UpgradeHistory;
use App\Models\User;
use App\Models\UserRevenue;
use Carbon\Carbon;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UploadFileMutation extends Mutation
{
    protected $attributes = [
        'name' => 'upgradeHistory',
        'description' => 'A mutation',
    ];

    protected $upgradeHistory;
    protected $refundHistory;
    protected $pack;

    public function __construct(UpgradeHistory $upgradeHistory, RefundHistory $refundHistory, Pack $pack)
    {
        $this->upgradeHistory = $upgradeHistory;
        $this->refundHistory = $refundHistory;
        $this->pack = $pack;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return Type::listOf(Type::string());
    }

    public function args(): array
    {
        return [
            'list' => [
                'type' => Type::listOf(GraphQL::type('UpgradeHistory')),
                'rules' => ['required'],
            ],
            'channel' => [
                'type' => Type::string(),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $user = auth()->user();
            $last30Days = now()->subDays(30)->endOfDay();
            $registerChannel = 'OTP';
            $errors = [];

            if ($args['channel'] === 'KH') {
                $branch_id = User::find($user->id)->branch_id;
                $branch = Branch::find($branch_id);
                $registerChannel = $branch?->register_channel ?? 'OTP';
            }

            //Lấy danh sách nâng cấp theo file up lên
            foreach ($args['list'] as $item) {
                $phone = $item['phone_number'];
                $code = $item['code'];
                $createdAt = $item['created_at'];

                //Tìm tất cả lệnh nâng cấp trong vòng 30 ngày
                if ($registerChannel === 'OTP') {
                    //Cập nhật tất cả các lệnh nâng cấp trong vòng 30 ngày thành OTP
                    $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();

                    UpgradeHistory::where('phone_number', $phone)
                        ->whereIn('user_id', $ids)
                        ->whereDate('created_at', '>=', $last30Days)
                        ->update([
                            'register_channel' => 'OTP',
                        ]);

                    $sumAmount = 0;
                    $sumAmount = UpgradeHistory::where('phone_number', $phone)
                        ->whereDate('created_at', '>', $last30Days)
                        ->whereNull('status')
                        ->sum('amount');

                    $lastestUpgrade = UpgradeHistory::where('phone_number', $phone)
                        ->whereNull('status')
                        ->where('code', $code)
                        ->whereDate('created_at', '<=', Carbon::parse($createdAt))
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (!$lastestUpgrade) {
                        $lastestUpgrade = UpgradeHistory::where('phone_number', $phone)
                            ->whereNull('status')
                            ->whereDate('created_at', '<=', Carbon::parse($createdAt))
                            ->orderBy('created_at', 'desc')
                            ->first();
                    }

                    if ($lastestUpgrade) {
                        //Xóa hết toàn bộ data nâng cấp cũ
                        UpgradeHistory::where('phone_number', $phone)
                            ->whereNull('status')
                            ->where('created_at', '>=', $last30Days)
                            ->delete();

                        //Hoàn dư bằng tổng hoàn trừ hoàn chuẩn
                        $res_amount = intval($lastestUpgrade->standard_amount ?? 0) - intval($sumAmount);
                        $refund_id = $lastestUpgrade->refund_id;

                        $pack = PackChangeHistory::where('code', $code)
                            ->where('created_at', '<=', Carbon::parse($createdAt))
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if (!$pack) {
                            $pack = Pack::where('code', $code)->first();
                        }

                        UpgradeHistory::create(
                            [
                                'phone_number' => $phone,
                                'code' => $code,
                                'amount' => $sumAmount,
                                'res_amount' => $res_amount,
                                'pack_price' => $pack->pack_price,
                                'revenue' => $pack->price,
                                'real_revenue' => $pack->revenue,
                                'standard_amount' => $pack->amount,
                                'err_amount' => 0,
                                'user_id' => $lastestUpgrade->user_id,
                                'status' => 1,
                                'register_channel' => 'OTP',
                                'refund_id' => $refund_id,
                                'channel' => $lastestUpgrade->channel,
                                'created_at' => $createdAt,
                                'updated_by' => $user->id,
                                'location_log' => 1,
                            ]);
                    } else {
                        array_push($errors, $phone);
                    }

                } else {
                    //Cập nhật tất cả các lệnh nâng cấp cùng chi nhánh trong vòng 30 ngày thành $registerChannel
                    $ids = User::where('branch_id', $user->branch_id)->pluck('id')->toArray();

                    UpgradeHistory::where('phone_number', $phone)
                        ->whereIn('user_id', $ids)
                        ->whereDate('created_at', '>=', $last30Days)
                        ->update([
                            'register_channel' => $registerChannel,
                        ]);

                    //Cập nhật hoàn lỗi cho các lệnh nâng cấp ko cùng KH
                    $upgradeListError = UpgradeHistory::where('phone_number', $phone)
                        ->whereNull('status')
                        ->where('register_channel', '<>', $registerChannel)
                        ->whereDate('created_at', '>=', $last30Days)
                        ->orderBy('created_at', 'desc')
                        ->get();

                    foreach ($upgradeListError as $upgrade) {
                        $u = UpgradeHistory::find($upgrade->id);
                        $u->err_amount = $u->amount;
                        $u->status = 402;
                        $u->save();

                    }

                    $sumAmount = 0;

                    $sumAmount = UpgradeHistory::where('phone_number', $phone)
                        ->whereDate('created_at', '>', $last30Days)
                        ->whereNull('status')
                        ->sum('amount');

                    $lastestUpgrade = UpgradeHistory::where('phone_number', $phone)
                        ->whereNull('status')
                        ->where('register_channel', $registerChannel)
                        ->where('code', $code)
                        ->whereDate('created_at', '>=', $last30Days)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    if (!$lastestUpgrade) {
                        $lastestUpgrade = UpgradeHistory::where('phone_number', $phone)
                            ->whereNull('status')
                            ->where('register_channel', $registerChannel)
                            ->whereDate('created_at', '>=', $last30Days)
                            ->orderBy('created_at', 'desc')
                            ->first();
                    }

                    if ($lastestUpgrade) {
                        //Xóa hết toàn bộ data nâng cấp cũ
                        UpgradeHistory::where('phone_number', $phone)
                            ->whereNull('status')
                            ->where('created_at', '>=', $last30Days)
                            ->delete();

                        //Hoàn dư bằng tổng hoàn trừ hoàn chuẩn
                        $res_amount = intval($lastestUpgrade->standard_amount ?? 0) - intval($sumAmount);
                        $refund_id = $lastestUpgrade->refund_id;

                        $pack = PackChangeHistory::where('code', $code)
                            ->where('created_at', '<=', Carbon::parse($createdAt))
                            ->orderBy('created_at', 'desc')
                            ->first();

                        if (!$pack) {
                            $pack = Pack::where('code', $code)->first();
                        }

                        UpgradeHistory::create(
                            [
                                'phone_number' => $phone,
                                'code' => $code,
                                'amount' => $sumAmount,
                                'res_amount' => $res_amount,
                                'pack_price' => $pack->pack_price,
                                'revenue' => $pack->price,
                                'real_revenue' => $pack->revenue,
                                'standard_amount' => $pack->amount,
                                'err_amount' => 0,
                                'user_id' => $lastestUpgrade->user_id,
                                'status' => 1,
                                'register_channel' => $registerChannel,
                                'refund_id' => $refund_id,
                                'channel' => $lastestUpgrade->channel,
                                'created_at' => $createdAt,
                                'updated_by' => $user->id,
                                'location_log' => 2,
                            ]);

                    } else {
                        array_push($errors, $phone);
                    }
                }
            }
            \DB::commit();
            UserRevenue::getUserRevenue();

            return $errors;
        } catch (\Exception $e) {
            \DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
