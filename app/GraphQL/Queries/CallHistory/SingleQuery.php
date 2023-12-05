<?php

namespace App\GraphQL\Queries\CallHistory;

use App\Models\CallHistory;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SingleQuery extends Query
{
    protected $attributes = [
        'name' => 'CallHistory',
    ];

    protected $model;

    public function __construct(CallHistory $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('CallHistory');
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
        $data = $this->model->with('user');
        // if (isset($args['pack_code'])) {
        //     $data = $data->where('pack_code', $args['pack_code']);
        // }

        // if (isset($args['branch_id'])) {
        //     $data = $data->where('branch_id', $args['branch_id']);
        // }
        return $data->first();
    }
}
