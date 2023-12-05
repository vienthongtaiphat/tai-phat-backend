<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class PackType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Pack',
        'description' => 'A type of Pack',
    ];

    public function fields(): array
    {
        return [
            'code' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'duration' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'amount' => [
                'type' => Type::int(),
            ],
            'pack_price' => [
                'type' => Type::int(),
            ],
            'price' => [
                'type' => Type::int(),
            ],
            'revenue' => [
                'type' => Type::int(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
            'description' => [
                'type' => Type::string(),
            ],
        ];
    }
}
