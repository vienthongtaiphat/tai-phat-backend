<?php

namespace App\GraphQL\Queries\News;

use App\GraphQL\ArrayToPaginate;
use App\Models\News;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class NewssQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'Newss',
    ];

    public function __construct(News $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('News');
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
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $data = $this->model->scopeSearch($args);
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($data->get()->toArray()) : $data->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
