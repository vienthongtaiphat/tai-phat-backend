<?php

namespace App\GraphQL\Types;

use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\InputType;

class BranchInputType extends InputType
{
    protected $attributes = [
        'name' => 'BranchInputType',
        'description' => 'A type of BranchInput',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'rate' => [
                'type' => Type::int(),
            ],
        ];
    }
}
