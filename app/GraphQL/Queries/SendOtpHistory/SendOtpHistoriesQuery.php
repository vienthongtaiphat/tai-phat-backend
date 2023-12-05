<?php

namespace App\GraphQL\Queries\SendOtpHistory;

use App\GraphQL\ArrayToPaginate;
use App\Models\SendOtpHistory;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SendOtpHistoriesQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'SendOtpHistories',
    ];

    public function __construct(SendOtpHistory $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('SendOtpHistory');
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