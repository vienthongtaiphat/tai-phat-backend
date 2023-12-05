<?php

namespace App\GraphQL\Queries\Pack;

use App\Models\Pack;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class PackQuery extends Query
{
    protected $attributes = [
        'name' => 'Pack',
    ];

    protected $model;

    public function __construct(Pack $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('Pack');
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
