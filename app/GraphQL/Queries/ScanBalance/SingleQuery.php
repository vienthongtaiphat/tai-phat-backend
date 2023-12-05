<?php

namespace App\GraphQL\Queries\ScanBalance;

use App\Models\ScanBalance;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SingleQuery extends Query
{
    protected $attributes = [
        'name' => 'ScanBalance',
    ];

    protected $model;

    public function __construct(ScanBalance $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('ScanBalance');
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
