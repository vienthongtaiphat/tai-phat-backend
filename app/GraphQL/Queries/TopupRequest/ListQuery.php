<?php

namespace App\GraphQL\Queries\TopupRequest;

use App\GraphQL\ArrayToPaginate;
use App\Models\TopupRequest;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class ListQuery extends Query
{
    protected $topupRequest;

    protected $attributes = [
        'name' => 'TopupRequestList',
    ];

    public function __construct(TopupRequest $topupRequest)
    {
        $this->topupRequest = $topupRequest;
    }

    public function type(): Type
    {
        return GraphQL::paginate('TopupRequest');
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
            'branch_id' => [
                'type' => Type::int(),
            ],
            'channel' => [
                'type' => Type::int(),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'from_date' => [
                'type' => Type::string(),
            ],
            'to_date' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $query = $this->topupRequest->scopeSearch($args);
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($query->get()->toArray()) : $query->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
