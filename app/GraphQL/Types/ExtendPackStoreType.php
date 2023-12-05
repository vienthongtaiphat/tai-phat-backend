<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ExtendPackStoreType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ExtendPackStoreType',
        'description' => 'A type of ExtendPackStoreType',
    ];

    public function fields(): array
    {
        return [
            'pack_code' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'branch_id' => [
                'type' => Type::int(),
            ],
            'branch' => [
                'type' => GraphQL::type('Branch'),
            ],
            'amount' => [
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
