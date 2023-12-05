<?php

namespace App\GraphQL\Queries\CtvUser;

use App\Models\CtvUser;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class CtvUserQuery extends Query
{
    protected $attributes = [
        'name' => 'CtvUser',
    ];

    public function type(): Type
    {
        return GraphQL::type('CtvUser');
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
        $data = CtvUser::findOrFail($args['id']);
        return $data;
    }
}
