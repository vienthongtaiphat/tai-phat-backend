<?php

namespace App\GraphQL\Mutations\Event;

use App\Models\Event;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createNews',
        'description' => 'Event mutation',
    ];

    protected $model;

    public function __construct(Event $model)
    {
        $this->model = $model;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role !== config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('Event');
    }

    public function args(): array
    {
        return [
            'title' => [
                'type' => Type::string(),
            ],
            'content' => [
                'type' => Type::string(),
            ],
            'short_content' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $res = $this->model->create(
            [
                'title' => $args['title'],
                'content' => $args['content'],
                'short_content' => $args['short_content'],
            ]);

        return $res;
    }
}
