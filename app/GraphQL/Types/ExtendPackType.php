<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class ExtendPackType extends GraphQLType
{
    protected $attributes = [
        'name' => 'ExtendPackType',
        'description' => 'A type of ExtendPackType',
    ];

    public function fields(): array
    {
        return [
            'code' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'revenue' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'real_revenue' => [
                'type' => Type::nonNull(Type::int()),
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
