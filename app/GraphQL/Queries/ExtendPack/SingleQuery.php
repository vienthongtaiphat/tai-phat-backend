<?php

namespace App\GraphQL\Queries\ExtendPack;

use App\Models\ExtendPack;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SingleQuery extends Query
{
    protected $attributes = [
        'name' => 'ExtendPack',
    ];

    protected $model;

    public function __construct(ExtendPack $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('ExtendPack');
    }

    public function args(): array
    {
        return [
            'code' => [
                'type' => Type::String(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $data = $this->model;
        if (isset($args['code'])) {
            $data = $data->where('code', $args['code']);
        }
        return $data->first();
    }
}
