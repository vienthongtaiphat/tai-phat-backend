<?php
namespace App\GraphQL\Mutations\UpgradeHistory;

use App\Models\RefundHistory;
use App\Models\UpgradeHistory;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateExtendPack',
        'description' => 'A mutation',
    ];

    protected $upgradeHistory;
    protected $refundHistory;

    public function __construct(UpgradeHistory $upgradeHistory, RefundHistory $refundHistory)
    {
        $this->upgradeHistory = $upgradeHistory;
        $this->refundHistory = $refundHistory;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('UpgradeHistory');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'status' => [
                'type' => Type::int(),
            ],
            'register_channel' => [
                'type' => Type::string(),
            ],
            'gift_type' => [
                'type' => Type::int(),
            ],
            'res_amount' => [
                'type' => Type::int(),
            ],
            'err_amount' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $upgradeHistory = $this->upgradeHistory->where('id', $args['id']);
        $oldUpgrade = $upgradeHistory->first();

        if (isset($args['status'])) {
            //Update status
            $upgradeHistory->update([
                'status' => $args['status'],
                'err_amount' => 0,
            ]);
        } else {
            $resAmount = $args['res_amount'];
            if ($args['gift_type']) {
                $resAmount = 0;
            }
            $upgradeHistory->update([
                'res_amount' => $resAmount,
                'err_amount' => $args['err_amount'],
            ]);

            $refundHistory = $this->refundHistory->findOrFail($oldUpgrade->refund_id);
            $refundHistory->gift_type = $args['gift_type'];
            if ($args['gift_type']) {
                $refundHistory->amount_tran = 0;
            }
            $refundHistory->register_channel = $args['register_channel'];
            $refundHistory->save();
        }

        return $upgradeHistory->first();
    }
}
