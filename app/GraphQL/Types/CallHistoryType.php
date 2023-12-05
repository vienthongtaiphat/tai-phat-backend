<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class CallHistoryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'CallHistoryType',
        'description' => 'A type of CallHistoryType',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'user_name' => [
                'type' => Type::string(),
            ],
            'accountcode' => [
                'type' => Type::string(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
            'branch_name' => [
                'type' => Type::string(),
            ],
            'count' => [
                'type' => Type::int(),
            ],
            'totalDuration' => [
                'type' => Type::int(),
            ],
            'totalWaitingTime' => [
                'type' => Type::int(),
            ],
            'disposition' => [
                'type' => Type::int(),
            ],
        ];
    }
}
