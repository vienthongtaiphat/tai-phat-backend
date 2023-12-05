<?php

namespace App\GraphQL\Queries\PayDebtRequest;

use App\GraphQL\ArrayToPaginate;
use App\Models\PayDebtRequest;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class ListQuery extends Query
{
    protected $payDebtRequest;

    protected $attributes = [
        'name' => 'PayDebtList',
    ];

    public function __construct(PayDebtRequest $payDebtRequest)
    {
        $this->payDebtRequest = $payDebtRequest;
    }

    public function type(): Type
    {
        return GraphQL::paginate('PayDebtRequest');
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
        $query = $this->payDebtRequest->scopeSearch($args);
        return $args['limit'] === 0 || isset($args['branch_id']) || isset($args['from_date']) || isset($args['to_date']) ? ArrayToPaginate::paginate($query->get()->toArray()) : $query->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
