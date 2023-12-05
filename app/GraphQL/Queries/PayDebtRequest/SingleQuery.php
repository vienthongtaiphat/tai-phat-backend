<?php

namespace App\GraphQL\Queries\PayDebtRequest;

use App\Models\PayDebtRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SingleQuery extends Query
{
    protected $attributes = [
        'name' => 'PayDebtRequest',
    ];

    protected $model;

    public function __construct(PayDebtRequest $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('PayDebtRequest');
    }

    public function args(): array
    {
        return [
            'pack_code' => [
                'type' => Type::string(),
            ],
            'branch_id' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $data = $this->model->with('branch', 'requestUser', 'approvedBy');
        // if (isset($args['pack_code'])) {
        //     $data = $data->where('pack_code', $args['pack_code']);
        // }

        // if (isset($args['branch_id'])) {
        //     $data = $data->where('branch_id', $args['branch_id']);
        // }
        return $data->first();
    }
}
