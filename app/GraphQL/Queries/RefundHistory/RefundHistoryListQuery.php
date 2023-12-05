<?php

namespace App\GraphQL\Queries\RefundHistory;

use App\Models\RefundHistory;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class RefundHistoryListQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'RefundHistory list',
    ];

    public function __construct(RefundHistory $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('RefundHistory');
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
            'phone_number' => [
                'type' => Type::string(),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'is_exist' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $query = $this->model->scopeSearch($args)->orderBy('created_at', 'desc');
        return $args['limit'] === 0 ? $query->get() : $query->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
