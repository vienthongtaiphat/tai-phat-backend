<?php

namespace App\GraphQL\Mutations\News;

use App\Models\News;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteNews',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(News $model)
    {
        $this->model = $model;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role !== config('constants.employee');
    }

    public function type(): Type
    {
        return Type::boolean();
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        return $this->model->where('id', $args['id'])->delete();
    }
}
