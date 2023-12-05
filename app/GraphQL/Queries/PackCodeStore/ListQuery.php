<?php

namespace App\GraphQL\Queries\PackCodeStore;

use App\GraphQL\ArrayToPaginate;
use App\Models\PackCodeStore;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class ListQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'PackCodeList',
    ];

    public function __construct(PackCodeStore $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('PackCodeStore');
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::int(),
                'default' => 50,
            ],
            'page' => [
                'type' => Type::int(),
                'default' => 1,
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $query = $this->model->scopeSearch()->orderBy('id', 'desc');
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($query->get()->toArray()) : $query->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
