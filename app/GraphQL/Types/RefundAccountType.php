<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class RefundAccountType extends GraphQLType
{
    protected $attributes = [
        'name' => 'RefundAccountType',
        'description' => 'A type of RefundAccountType',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'username' => [
                'type' => Type::string(),
            ],
            'branch' => [
                'type' => GraphQL::type('Branch'),
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
