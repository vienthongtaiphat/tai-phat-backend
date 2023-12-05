<?php

namespace App\GraphQL\Mutations\ExtendPack;

use App\Models\ExtendPack;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createPack',
        'description' => 'ExtendPack mutation',
    ];

    protected $extendPack;

    public function __construct(ExtendPack $extendPack)
    {
        $this->extendPack = $extendPack;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role !== config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('ExtendPack');
    }

    public function args(): array
    {
        return [
            'code' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'revenue' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
            ],
            'real_revenue' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $res = $this->extendPack->updateOrCreate(
            ['code' => $args['code']],
            [
                'revenue' => $args['revenue'],
                'real_revenue' => $args['real_revenue'],
            ]);

        return $res;
    }
}
