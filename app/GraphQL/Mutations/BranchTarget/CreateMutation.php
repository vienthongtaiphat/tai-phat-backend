<?php

namespace App\GraphQL\Mutations\BranchTarget;

use App\Models\BranchTarget;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createNews',
        'description' => 'BranchTarget mutation',
    ];

    protected $model;

    public function __construct(BranchTarget $model)
    {
        $this->model = $model;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role !== config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('BranchTarget');
    }

    public function args(): array
    {
        return [
            'branch_id' => [
                'type' => Type::int(),
            ],
            'target' => [
                'type' => Type::string(),
            ],
            'month' => [
                'type' => Type::int(),
            ],
            'year' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $this->model->where(
            [
                'branch_id' => $args['branch_id'],
                'month' => $args['month'],
                'year' => $args['year'],
            ])->delete();

        $res = $this->model->create(
            [
                'branch_id' => $args['branch_id'],
                'target' => $args['target'],
                'month' => $args['month'],
                'year' => $args['year'],
            ]);

        return $res;
    }
}
