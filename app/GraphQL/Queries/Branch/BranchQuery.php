<?php

namespace App\GraphQL\Queries\Branch;

use App\Models\Branch;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class BranchQuery extends Query
{
    protected $attributes = [
        'name' => 'Branch',
    ];

    protected $model;

    public function __construct(Branch $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('Branch');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::int()
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
