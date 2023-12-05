<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\InputType;

class SubscriptionType extends InputType
{
    protected $attributes = [
        'name' => 'Subscription',
        'description' => 'A type of Subscription',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'fileInfo' => [
                'type' => GraphQL::type('FileUploadedHistory'),
            ],
            'phone_number' => [
                'type' => Type::string(),
            ],
            'phone_type' => [
                'type' => Type::string(),
            ],
            'code' => [
                'type' => Type::string(),
            ],
            'period' => [
                'type' => Type::int(),
            ],
            'first_register_date' => [
                'type' => Type::string(),
            ],
            'first_expired_date' => [
                'type' => Type::string(),
            ],
            'register_date' => [
                'type' => Type::string(),
            ],
            'expired_date' => [
                'type' => Type::string(),
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