<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class LogType extends GraphQLType
{
    protected $attributes = [
        'name' => 'LogType',
        'description' => 'A type of LogType',
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
            'logType' => [
                'type' => GraphQL::type('LogType'),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'params' => [
                'type' => Type::string(),
            ],
            'log_type_id' => [
                'type' => Type::int(),
            ],
            'is_exist' => [
                'type' => Type::int(),
            ],
            'total_exist' => [
                'type' => Type::int(),
            ],
            'total_not_exist' => [
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
