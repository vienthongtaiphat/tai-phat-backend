<?php

namespace App\GraphQL\Queries\Subscription;

use App\GraphQL\ArrayToPaginate;
use App\Models\Subscription;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SubscriptionsQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'Subscriptions',
    ];

    public function __construct(Subscription $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('SubscriptionQuery');
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::int(),
            ],
            'page' => [
                'type' => Type::int(),
                'default' => 1,
            ],
            'register_date' => [
                'type' => Type::string(),
            ],
            'expired_date' => [
                'type' => Type::string(),
            ],
            'code' => [
                'type' => Type::string(),
                'default' => null,
            ],
            'khcn_code' => [
                'type' => Type::string(),
                'default' => null,
            ],
            'phone_type' => [
                'type' => Type::string(),
                'default' => null,
            ],
            'assign_status' => [
                'type' => Type::int(),
                'default' => null,
            ],
            'status' => [
                'type' => Type::int(),
                'default' => null,
            ],
            'file_id' => [
                'type' => Type::int(),
                'default' => null,
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $data = $this->model->scopeSearch($args);
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($data->get()->toArray()) : $data->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
