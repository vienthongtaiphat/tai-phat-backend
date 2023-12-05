<?php

namespace App\GraphQL\Queries\SendOtpHistory;

use App\Models\SendOtpHistory;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SendOtpHistoryQuery extends Query
{
    protected $attributes = [
        'name' => 'SendOtpHistory',
    ];

    protected $model;

    public function __construct(SendOtpHistory $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('SendOtpHistory');
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
