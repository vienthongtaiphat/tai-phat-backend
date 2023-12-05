<?php

namespace App\GraphQL\Queries\Log;

use App\Models\Log;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class LogQuery extends Query
{
    protected $attributes = [
        'name' => 'Log',
    ];
    protected $model;

    public function __construct(Log $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('Log');
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
