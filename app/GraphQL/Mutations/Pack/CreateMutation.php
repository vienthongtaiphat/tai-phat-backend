<?php

namespace App\GraphQL\Mutations\Pack;

use App\Models\Pack;
use App\Models\PackChangeHistory;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createPack',
        'description' => 'Pack mutation',
    ];

    protected $model;

    public function __construct(Pack $model)
    {
        $this->model = $model;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role !== config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('Pack');
    }

    public function args(): array
    {
        return [
            'code' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'duration' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
            ],
            'amount' => [
                'type' => Type::int(),
            ],
            'pack_price' => [
                'type' => Type::int(),
            ],
            'price' => [
                'type' => Type::int(),
            ],
            'revenue' => [
                'type' => Type::int(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $res = $this->model->updateOrCreate(
            ['code' => $args['code']],
            [
                'pack_price' => $args['pack_price'],
                'price' => $args['price'],
                'duration' => $args['duration'],
                'amount' => $args['amount'],
                'revenue' => $args['revenue'],
                'description' => $args['description'],
            ]);

        PackChangeHistory::create([
            'code' => $args['code'],
            'pack_price' => $args['pack_price'],
            'price' => $args['price'],
            'amount' => $args['amount'],
            'revenue' => $args['revenue'],
        ]);

        return $res;
    }
}