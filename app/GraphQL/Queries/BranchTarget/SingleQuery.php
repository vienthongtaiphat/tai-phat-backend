<?php

namespace App\GraphQL\Queries\BranchTarget;

use App\Models\BranchTarget;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SingleQuery extends Query
{
    protected $attributes = [
        'name' => 'BranchTarget',
    ];

    protected $model;

    public function __construct(BranchTarget $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('BranchTarget');
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
