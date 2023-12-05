<?php

namespace App\GraphQL\Queries\News;

use App\Models\News;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class NewsQuery extends Query
{
    protected $attributes = [
        'name' => 'News',
    ];
    protected $model;

    public function __construct(News $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('News');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $data = $this->model;
        if (isset($args['id'])) {
            $data = $data->where('id', $args['id']);
        }
        return $data->first();
    }
}
