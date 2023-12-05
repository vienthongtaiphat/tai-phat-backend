<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class TopupRequestType extends GraphQLType
{
    protected $attributes = [
        'name' => 'TopupRequestType',
        'description' => 'A type of TopupRequestType',
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
            'requestUser' => [
                'type' => GraphQL::type('User'),
            ],
            'approvedBy' => [
                'type' => GraphQL::type('User'),
            ],
            'status' => [
                'type' => Type::int(),
            ],
            'type' => [
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
