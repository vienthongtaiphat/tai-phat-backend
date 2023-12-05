<?php

namespace App\GraphQL\Mutations\Subscription;

use App\Models\Branch;
use App\Models\Subscription;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteSubscriptionMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteBranches',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(Branch $model)
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
            'ids' => [
                'type' => Type::listOf(Type::int()),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $res = $this->model->whereIn('id', $args['ids']);

            $res = boolval($res->delete());
            if ($res) {
                Subscription::whereIn('branch_id', $args['ids'])
                    ->update(['branch_id' => null]);
            }
            \DB::commit();
            return $res;
        } catch (\Exception$e) {
            \DB::rollBack();
            return $e;
        }
    }
}
