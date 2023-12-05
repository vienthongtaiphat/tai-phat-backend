<?php

namespace App\GraphQL\Mutations\TopupRequest;

use App\Models\TopupRequest;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deletePayDebtRequest',
        'description' => 'A mutation',
    ];

    protected $topupRequest;

    public function __construct(TopupRequest $topupRequest)
    {
        $this->topupRequest = $topupRequest;
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
        $res = $this->topupRequest->whereIn('id', $args['ids']);
        return $res->delete();
    }
}
