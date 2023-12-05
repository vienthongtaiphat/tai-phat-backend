<?php

namespace App\GraphQL\Queries\FileUploadHistory;

use App\Models\FileUploadedHistory;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class FileUploadHistoryQuery extends Query
{
    protected $attributes = [
        'name' => 'FileUploadHistory',
    ];

    public function type(): Type
    {
        return GraphQL::type('FileUploadedHistory');
    }

    public function args(): array
    {
        return [
            'id' => [
                'name' => 'id',
                'type' => Type::int(),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $data = FileUploadedHistory::findOrFail($args['id']);
        return $data;
    }
}
