<?php

namespace App\GraphQL\Queries\DataLog;

use App\Models\DataLog;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class LogsTotalQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'LogsTotalQuery',
    ];

    public function __construct(DataLog $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('DataLog'));
    }

    public function args(): array
    {
        return [
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
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $data = $this->model->scopeSearchTotal($args)->get()->toArray();

        foreach ($data as $index => $log) {
            $data[$index]['total'] = $this->model->countByUser($log['user_id'], $args);
        }

        return $data;
    }
}
