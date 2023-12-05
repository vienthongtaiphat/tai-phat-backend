<?php

namespace App\GraphQL\Queries\User;

use App\GraphQL\ArrayToPaginate;
use App\Models\User;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class UsersQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'Users',
    ];

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('User');
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
            'branch_id' => [
                'type' => Type::int(),
                'default' => null,
            ],
            'role' => [
                'type' => Type::int(),
                'default' => null,
            ],
            'user_code' => [
                'type' => Type::string(),
                'default' => null,
            ],
            'name' => [
                'type' => Type::string(),
                'default' => null,
            ],
            'type' => [
                'type' => Type::int(),
                'default' => null,
            ],
            'activated' => [
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
