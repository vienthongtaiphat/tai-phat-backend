<?php

namespace App\GraphQL\Queries\CtvUser;

use App\Models\CtvUser;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class CtvUsersQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'CtvUsers',
    ];

    public function __construct(CtvUser $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('CtvUser'));
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $user = auth()->user();
        if ($user->role === config('constants.manager') || $user->role === config('constants.employee')) {
            $res = $this->model->where('branch_id', $user->branch_id)
                ->where('is_used', 1)->get();
        } else {
            $res = $this->model->all();
        }
        return $res;
    }
}
