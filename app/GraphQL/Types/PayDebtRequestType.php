<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class PayDebtRequestType extends GraphQLType
{
    protected $attributes = [
        'name' => 'PayDebtRequestType',
        'description' => 'A type of PayDebtRequestType',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'amount' => [
                'type' => Type::int(),
            ],
            'created_by' => [
                'type' => Type::int(),
            ],
            'branch_id' => [
                'type' => Type::int(),
            ],
            'branch' => [
                'type' => GraphQL::type('Branch'),
            ],
            'user' => [
                'type' => GraphQL::type('User'),
            ],
            'approved' => [
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
