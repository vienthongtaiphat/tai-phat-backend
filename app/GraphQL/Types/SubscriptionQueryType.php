<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class SubscriptionQueryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'SubscriptionQuery',
        'description' => 'A type of Subscription',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'fileInfo' => [
                'type' => GraphQL::type('FileUploadedHistory'),
            ],
            'phone_number' => [
                'type' => Type::string(),
            ],
            'total' => [
                'type' => Type::int(),
            ],
            'phone_type' => [
                'type' => Type::string(),
            ],
            'code' => [
                'type' => Type::string(),
            ],
            'period' => [
                'type' => Type::int(),
            ],
            'register_by_user' => [
                'type' => GraphQL::type('User'),
            ],
            'assigned_to_user' => [
                'type' => GraphQL::type('User'),
            ],
            'upload_by_user' => [
                'type' => GraphQL::type('User'),
            ],
            'first_register_date' => [
                'type' => Type::string(),
            ],
            'first_expired_date' => [
                'type' => Type::string(),
            ],
            'balance' => [
                'type' => Type::int(),
            ],
            'assigned_to' => [
                'type' => Type::int(),
            ],
            'status' => [
                'type' => Type::int(),
            ],
            'note' => [
                'type' => Type::string(),
            ],
            'user_note' => [
                'type' => Type::string(),
            ],
            'register_date' => [
                'type' => Type::string(),
            ],
            'expired_date' => [
                'type' => Type::string(),
            ],
            'assigned_date' => [
                'type' => Type::string(),
            ],
            'branch' => [
                'type' => GraphQL::type('Branch'),
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
