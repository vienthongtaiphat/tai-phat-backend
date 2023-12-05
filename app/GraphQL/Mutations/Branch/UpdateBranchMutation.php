<?php
namespace App\GraphQL\Mutations\Branch;

use App\Models\Branch;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateBranchMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateBranch',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(Branch $model)
    {
        $this->model = $model;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('Branch');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'name' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $Branch = $this->model->findOrFail($args['id']);
        $Branch->fill($args);
        $Branch->save();

        return $Branch;
    }
}
