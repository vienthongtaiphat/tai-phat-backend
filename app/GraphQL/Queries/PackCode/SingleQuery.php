<?php

namespace App\GraphQL\Queries\PackCode;

use App\Models\PackCodeHistory;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SingleQuery extends Query
{
    protected $attributes = [
        'name' => 'PackCodeQuery',
    ];

    protected $model;

    public function __construct(PackCodeHistory $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('PackCodeHistory');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $data = $this->model;
        if (isset($args['id'])) {
            $data = $data->where('id', $args['id']);
        }
        return $data->first();
    }
}
