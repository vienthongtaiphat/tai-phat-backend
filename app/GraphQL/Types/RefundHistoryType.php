<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class RefundHistoryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'RefundHistoryType',
        'description' => 'A type of RefundHistoryType',
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
            'refundAccount' => [
                'type' => GraphQL::type('RefundAccount'),
            ],
            'amount' => [
                'type' => Type::int(),
            ],
            'channel' => [
                'type' => Type::int(),
            ],
            'amount_tran' => [
                'type' => Type::int(),
            ],
            'amount_discount' => [
                'type' => Type::int(),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'is_exist' => [
                'type' => Type::int(),
            ],
            'is_duplicate' => [
                'type' => Type::int(),
            ],
            'phone_number' => [
                'type' => Type::string(),
            ],
            'refcode' => [
                'type' => Type::string(),
            ],
            'id_tran' => [
                'type' => Type::int(),
            ],
            'status' => [
                'type' => Type::int(),
            ],
            'note' => [
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
            'register_channel' => [
                'type' => Type::string(),
            ],
            'gift_type' => [
                'type' => Type::int(),
            ],
        ];
    }
}
