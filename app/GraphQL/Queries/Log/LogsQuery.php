<?php

namespace App\GraphQL\Queries\Log;

use App\GraphQL\ArrayToPaginate;
use App\Models\Log;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class LogsQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'Logs',
    ];

    public function __construct(Log $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('Log');
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::int(),
                'default' => 6,
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
            'user_id' => [
                'type' => Type::int(),
            ],
            'branch_id' => [
                'type' => Type::int(),
            ],
            'is_exist' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $data = $this->model->scopeSearch($args);
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($data->get()->toArray()) : $data->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
