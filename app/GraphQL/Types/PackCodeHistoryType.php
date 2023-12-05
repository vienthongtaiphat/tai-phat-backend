<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class PackCodeHistoryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PackCodeHistory',
        'description' => 'A type of PackCodeHistory',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'pack_code' => [
                'type' => Type::string(),
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
