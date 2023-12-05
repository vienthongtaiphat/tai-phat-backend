<?php

namespace App\GraphQL\Types;

use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Type as GraphQLType;

class FileUploadedHistoryType extends GraphQLType
{
    protected $attributes = [
        'name' => 'FileUploadedHistory',
        'description' => 'A type of File Uploaded History',
    ];

    public function fields(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'file_name' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'upload_by' => [
                'type' => Type::int(),
            ],
            'activated' => [
                'type' => Type::int(),
            ],
            'total_subscriptions' => [
                'type' => Type::int(),
            ],
            'total_assigns' => [
                'type' => Type::int(),
            ],
            'total_active_subscriptions' => [
                'type' => Type::int(),
            ],
            'upload_by_user' => [
                'type' => GraphQL::type('User'),
            ],
            'assigns' => [
                'type' => Type::listOf(GraphQL::type('Assign')),
            ],
            'created_at' => [
                'type' => Type::string(),
            ],
            'updated_at' => [
                'type' => Type::string(),
            ],
            'duplicateDatas' => [
                'type' => Type::listOf(Type::string()),
            ],
        ];
    }
}
