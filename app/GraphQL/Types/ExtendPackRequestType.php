<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ExtendPackRequestType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ExtendPackRequestType',
        'description' => 'A type of ExtendPackRequestType',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'phone_number' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'pack_code' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'created_by' => [
                'type' => Type::int(),
            ],
            'requestUser' => [
                'type' => GraphQL::type('User'),
            ],
            'approvedBy' => [
                'type' => GraphQL::type('User'),
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
