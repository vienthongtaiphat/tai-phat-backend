<?php

namespace App\GraphQL\Mutations\PayDebtRequest;

use App\Models\PayDebtRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createPayDebtRequest',
        'description' => 'PayDebtRequest mutation',
    ];

    protected $payDebtRequest;

    public function __construct(PayDebtRequest $payDebtRequest)
    {
        $this->payDebtRequest = $payDebtRequest;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('PayDebtRequest');
    }

    public function args(): array
    {
        return [
            'amount' => [
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

        if (isset($args['created_at'])) {
            $res = $this->payDebtRequest->create(
                [
                    'amount' => $args['amount'],
                    'created_by' => $user->id,
                    'branch_id' => $user->branch_id,
                    'created_at' => $args['created_at'],
                ]);
        } else {
            $res = $this->payDebtRequest->create(
                [
                    'amount' => $args['amount'],
                    'created_by' => $user->id,
                    'branch_id' => $user->branch_id,
                ]);
        }
        return $res;
    }
}
