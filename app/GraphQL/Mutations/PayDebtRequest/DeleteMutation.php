<?php

namespace App\GraphQL\Mutations\PayDebtRequest;

use App\Models\PayDebtRequest;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deletePayDebtRequest',
        'description' => 'A mutation',
    ];

    protected $payDebtRequest;

    public function __construct(PayDebtRequest $payDebtRequest)
    {
        $this->payDebtRequest = $payDebtRequest;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role !== config('constants.employee');
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'ids' => [
                'type' => Type::listOf(Type::int()),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $res = $this->payDebtRequest->whereIn('id', $args['ids']);
        return $res->delete();
    }
}
