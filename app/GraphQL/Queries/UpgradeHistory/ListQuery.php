<?php

namespace App\GraphQL\Queries\UpgradeHistory;

use App\GraphQL\ArrayToPaginate;
use App\Models\UpgradeHistory;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class ListQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'UpgradeHistory list',
    ];

    public function __construct(UpgradeHistory $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('UpgradeHistory');
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
            'from_date' => [
                'type' => Type::string(),
            ],
            'to_date' => [
                'type' => Type::string(),
            ],
            'status' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $query = $this->model->scopeSearch($args);
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($this->model->get()->toArray()) : $query->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}