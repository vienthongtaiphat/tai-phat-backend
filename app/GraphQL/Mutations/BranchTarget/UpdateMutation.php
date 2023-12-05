<?php
namespace App\GraphQL\Mutations\BranchTarget;

use App\Models\BranchTarget;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateNews',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(BranchTarget $model)
    {
        $this->model = $model;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('BranchTarget');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'title' => [
                'type' => Type::string(),
            ],
            'content' => [
                'type' => Type::string(),
                'defaultValue' => '',
            ],
            'short_content' => [
                'type' => Type::string(),
                'defaultValue' => '',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $model = $this->model->find($args['id']);
        $model->title = $args['title'];
        $model->content = $args['content'];
        $model->short_content = $args['short_content'];
        $model->save();

        return $model;
    }
}