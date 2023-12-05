<?php
namespace App\GraphQL\Mutations\Branch;

use App\Models\Branch;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateBranchMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createBranch',
        'description' => 'Branch mutation',
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
            'name' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],

        ];
    }

    public function resolve($root, $args)
    {
        $Branch = $this->model->create(collect($args)->only(['name'])->toArray());
        return $Branch;
    }
}
