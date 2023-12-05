<?php

namespace App\GraphQL\Mutations\TopupRequest;

use App\Models\Branch;
use App\Models\TopupRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createPayDebtRequest',
        'description' => 'TopupRequest mutation',
    ];

    protected $topupRequest;

    public function __construct(TopupRequest $topupRequest)
    {
        $this->topupRequest = $topupRequest;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('TopupRequest');
    }

    public function args(): array
    {
        return [
            'amount' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
            ],
            'type' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
            ],
            'branch_id' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $user = auth()->user();

        $res = $this->topupRequest->create(
            [
                'amount' => $args['amount'],
                'type' => $args['type'],
                'created_by' => $user->id,
                'branch_id' => $args['branch_id'],
                'created_at' => isset($args['created_at']) ? $args['created_at'] : now(),
            ]);

        //send Telegram to Admin group
        $topupChannelId = "-974537398";
        $channel = $args['type'] === 1 ? '24H' : 'EZ';
        $branch = Branch::find($args['branch_id']);
        $message = "Duyệt tiền nạp: " . number_format($args['amount']) .
        "\nKênh nạp: " . $channel .
        "\nCN: " . $branch->display_name;
        \App\Helpers\SendTelegram::instance()->sendMessage($topupChannelId, $message);
        return $res;
    }
}
