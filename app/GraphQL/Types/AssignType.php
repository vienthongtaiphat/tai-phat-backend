<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class AssignType extends GraphQLType
{
    protected $attributes = [
        'name' => 'AssignType',
        'description' => 'A type of AssignType',
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
            'file' => [
                'type' => GraphQL::type('FileUploadedHistory'),
            ],
            'user_id' => [
                'type' => Type::int(),
            ],
            'file_id' => [
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
