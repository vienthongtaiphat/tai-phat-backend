<?php

namespace App\GraphQL\Queries\DataLog;

use App\Models\DataLog;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class LogQuery extends Query
{
    protected $attributes = [
        'name' => 'DataLog',
    ];
    protected $model;

    public function __construct(DataLog $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('DataLog');
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
