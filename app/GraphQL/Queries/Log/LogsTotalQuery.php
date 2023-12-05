<?php

namespace App\GraphQL\Queries\Log;

use App\Models\Log;
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

    public function __construct(Log $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('Log'));
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
            'is_exist' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $data = $this->model->scopeSearchTotal($args)->get()->toArray();

        foreach ($data as $index => $log) {
            $data[$index]['total_exist'] = !isset($args['is_exist']) || $args['is_exist'] ? $this->model->countByUser($log['user_id'], 1, $args) : 0;
            $data[$index]['total_not_exist'] = !isset($args['is_exist']) || !$args['is_exist'] ? $this->model->countByUser($log['user_id'], 0, $args) : 0;
        }

        return $data;
    }
}
