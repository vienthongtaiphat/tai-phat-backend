<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class UpgradeHistoryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'UpgradeHistoryType',
        'description' => 'A type of UpgradeHistoryType',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'user' => [
                'type' => GraphQL::type('User'),
            ],
            'pack' => [
                'type' => GraphQL::type('Pack'),
            ],
            'refund' => [
                'type' => GraphQL::type('RefundHistory'),
            ],
            'amount' => [
                'type' => Type::int(),
            ],
            'channel' => [
                'type' => Type::int(),
            ],
            'res_amount' => [
                'type' => Type::int(),
            ],
            'err_amount' => [
                'type' => Type::int(),
            ],
            'standard_amount' => [
                'type' => Type::int(),
            ],
            'revenue' => [
                'type' => Type::int(),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'phone_number' => [
                'type' => Type::string(),
            ],
            'code' => [
                'type' => Type::string(),
            ],
            'status' => [
                'type' => Type::int(),
            ],
            'gift_type' => [
                'type' => Type::int(),
            ],
            'note' => [
                'type' => Type::string(),
            ],
            'channel' => [
                'type' => Type::string(),
            ],
            'register_channel' => [
                'type' => Type::string(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
            'total_amount' => [
                'type' => Type::int(),
            ],
        ];
    }
}
