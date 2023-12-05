<?php

namespace App\GraphQL\Mutations\ScanBalance;

use App\Models\ScanBalance;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createNews',
        'description' => 'ScanBalance mutation',
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
            'balance' => [
                'type' => Type::int(),
            ],
            'phone_number' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $count = $this->model->where('user_id', auth()->user()->id)->count();
        if ($count >= 20) {
            throw new \Exception('Đã hết số lượt quét');
        }
        $p = \App\Helpers\Utils::instance()->trimPhoneNumber($args['phone_number']);
        $res = $this->model->where('phone_number', $p)->first();

        if ($res) {
            throw new \Exception('Số điện thoại đã được tạo');
        }
        $res = $this->model->create(
            [
                'phone_number' => $p,
                'balance' => $args['balance'],
                'user_id' => auth()->user()->id,
            ]);

        return $res;
    }
}
