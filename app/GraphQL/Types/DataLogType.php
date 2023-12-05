<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class DataLogType extends GraphQLType
{
    protected $attributes = [
        'name' => 'DataLogType',
        'description' => 'A type of DataLogType',
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
            'user_id' => [
                'type' => Type::int(),
            ],
            'total' => [
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
