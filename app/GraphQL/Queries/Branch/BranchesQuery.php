<?php

namespace App\GraphQL\Queries\Branch;

use App\GraphQL\ArrayToPaginate;
use App\Models\Branch;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class BranchesQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'Branches',
    ];

    public function __construct(Branch $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Branch'));
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::int(),
                'default' => 0,
            ],
            'page' => [
                'type' => Type::int(),
                'default' => 1,
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($this->model->orderBy('created_at', 'desc')->get()->toArray()) : $this->model->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
