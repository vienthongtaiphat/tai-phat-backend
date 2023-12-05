<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class BranchType extends GraphQLType
{
    protected $attributes = [
        'name' => 'Branch',
        'description' => 'A type of Branch',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'name' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'total_members' => [
                'type' => Type::int(),
            ],
            'total_subscriptions' => [
                'type' => Type::int(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
            'display_name' => [
                'type' => Type::string(),
            ],
        ];
    }
}
