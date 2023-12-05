<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ScanBalanceType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ScanBalanceType',
        'description' => 'A type of ScanBalanceType',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'user' => [
                'type' => GraphQL::type('User'),
            ],
            'balance' => [
                'type' => Type::int(),
            ],
            'current_balance' => [
                'type' => Type::int(),
            ],
            'phone_number' => [
                'type' => Type::string(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
        ];
    }
}
