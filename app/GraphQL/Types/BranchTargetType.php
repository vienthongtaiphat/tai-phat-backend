<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class BranchTargetType extends GraphQLType
{
    protected $attributes = [
        'name' => 'BranchTargetType',
        'description' => 'A type of BranchTargetType',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'branch_id' => [
                'type' => Type::string(),
            ],
            'branch' => [
                'type' => GraphQL::type('Branch'),
            ],
            'target' => [
                'type' => Type::string(),
            ],
            'month' => [
                'type' => Type::int(),
            ],
            'year' => [
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
