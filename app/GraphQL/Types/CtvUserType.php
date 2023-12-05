<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class CtvUserType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CtvUser',
        'description' => 'A type of CtvUsers',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'display_name' => [
                'type' => Type::string(),
            ],
            'email' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'phone' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'total' => [
                'type' => Type::int(),
            ],
            'branch_id' => [
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