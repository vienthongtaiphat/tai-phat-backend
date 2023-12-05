<?php

namespace App\GraphQL\Mutations\UpgradeHistory;

use App\Models\RefundHistory;
use App\Models\UpgradeHistory;
use Carbon\Carbon;
use Exception;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createPack',
        'description' => 'UpgradeHistory mutation',
    ];

    protected $upgradeHistory;
    protected $refundHistory;

    public function __construct(UpgradeHistory $upgradeHistory, RefundHistory $refundHistory)
    {
        $this->upgradeHistory = $upgradeHistory;
        $this->refundHistory = $refundHistory;
    }

    // public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    // {
    //     return auth()->user()->role !== config('constants.employee');
    // }

    public function type(): Type
    {
        return GraphQL::type('UpgradeHistory');
    }

    public function args(): array
    {
        return [
            'phone_number' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'code' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'channel' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $phoneNumber = \App\Helpers\Utils::instance()->trimPhoneNumber($args['phone_number']);
        $refund = $this->refundHistory
            ->orderBy('id', 'desc')
            ->first();

        $sumOfRefunds = $this->refundHistory->where('phone_number', $phoneNumber)
            ->where('code', $args['code'])
            ->where('created_at', '>', Carbon::now()->subDays(30)->endOfDay())
            ->orderByDesc('id')
            ->sum('amount');

        if ($refund) {
            $upgrade = $this->upgradeHistory->where([
                'phone_number' => $phoneNumber,
                'code' => $args['code']])
                ->first();

            if ($upgrade) {
                throw new Exception('Yêu cầu nâng cấp đã tồn tại');
            }
        }

        throw new Exception('Thuê bao chưa tạo yêu cầu hoàn tiền');
    }
}
