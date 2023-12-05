<?php

declare (strict_types = 1);

namespace App\GraphQL\Mutations\User;

use App\Models\Branch;
use App\Models\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateUserMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateUser',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    // public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    // {
    //     return auth()->user()->role !== config('constants.employee');
    // }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::int(),
            ],
            'user_code' => [
                'type' => Type::string(),
            ],
            'name' => [
                'type' => Type::string(),
            ],
            'email' => [
                'type' => Type::string(),
            ],
            'phone' => [
                'type' => Type::string(),
                'rules' => ['min:6', 'max:20'],
            ],
            'password' => [
                'type' => Type::string(),
                'rules' => ['min:6'],
            ],
            'identity_card' => [
                'type' => Type::string(),
                'default' => null,
            ],
            'fcm_token' => [
                'type' => Type::string(),
            ],
            'branch_id' => [
                'type' => Type::int(),
            ],
            'role' => [
                'type' => Type::int(),
            ],
            'activated' => [
                'type' => Type::int(),
            ],
            'type' => [
                'type' => Type::int(),
            ],
            'line_call' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $id = isset($args['id']) ? $args['id'] : auth()->id();
            $user = $this->model->findOrFail($id);
            if ($user && isset($args['branch_id']) && $args['branch_id'] !== $user->branch_id) {
                $old_branch = $user->branch_id;

                //hủy branch cũ
                $oldBranch = Branch::find($old_branch);
                $oldBranch->total_members = intval($oldBranch->total_members ?? 1) - 1;
                $oldBranch->save();

                //cộng branch mới
                $newBranch = Branch::find($args['branch_id']);
                $newBranch->total_members = intval($newBranch->total_members ?? 0) + 1;
                $newBranch->save();
            }

            if (isset($args['password'])) {
                $args['password'] = bcrypt($args['password']);
            }
            $user->fill($args);
            $user->save();

            \DB::commit();
            return $user;
        } catch (\Exception $e) {
            \DB::rollBack();
        }
    }
}