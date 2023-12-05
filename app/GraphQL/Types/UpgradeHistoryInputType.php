<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\InputType;

class UpgradeHistoryInputType extends InputType
{
    protected $attributes = [
        'name' => 'UpgradeHistoryInputType',
        'description' => 'A type of BranchInput',
    ];

    public function fields(): array
    {
        return [
            'phone_number' => [
                'type' => Type::string(),
            ],
            'code' => [
                'type' => Type::string(),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
        ];
    }
}
