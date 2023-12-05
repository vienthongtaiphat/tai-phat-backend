<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class SendOtpHistoryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'SendOtpHistory',
        'description' => 'A type of SendOtpHistoryType',
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
            'total' => [
                'type' => Type::int(),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'phone_number' => [
                'type' => Type::string(),
            ],
            'resp_content' => [
                'type' => Type::string(),
            ],
            'code' => [
                'type' => Type::string(),
            ],
            'status' => [
                'type' => Type::int(),
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
