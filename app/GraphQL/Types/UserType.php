<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class UserType extends GraphQLType
{
    protected $attributes = [
        'name' => 'User',
        'description' => 'A type of user',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'user_code' => [
                'type' => Type::string(),
            ],
            'identity_card' => [
                'type' => Type::string(),
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'email' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'phone' => [
                'type' => Type::string(),
            ],
            'username' => [
                'type' => Type::string(),
            ],
            'type' => [
                'type' => Type::int(),
            ],
            'activated' => [
                'type' => Type::int(),
            ],
            'role' => [
                'type' => Type::int(),
            ],
            'fcm_token' => [
                'type' => Type::string(),
            ],
            'branch_id' => [
                'type' => Type::int(),
            ],
            'branch' => [
                'type' => GraphQL::type('Branch'),
            ],
            'total' => [
                'type' => Type::int(),
            ],
            'total_subscriptions' => [
                'type' => Type::int(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
            'deleted_at' => [
                'type' => Type::string(),
            ],
            'line_call' => [
                'type' => Type::string(),
            ],
        ];
    }
}