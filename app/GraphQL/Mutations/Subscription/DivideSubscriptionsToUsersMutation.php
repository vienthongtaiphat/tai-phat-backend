<?php

namespace App\GraphQL\Mutations\Subscription;

use App\Models\Subscription;
use App\Models\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DivideSubscriptionsToUsersMutation extends Mutation
{
    protected $attributes = [
        'name' => 'DivideSubscriptionsToUsersMutation',
        'description' => 'DivideSubscriptionsToUsersMutation',
    ];

    protected $subscription;
    protected $user;

    public function __construct(Subscription $subscription, User $user)
    {
        $this->subscription = $subscription;
        $this->user = $user;
    }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    public function args(): array
    {
        return [
            'assign_to_users' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
            ],
            'list' => [
                'type' => Type::nonNull(Type::listOf(Type::string())),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $userId = auth()->user()->id;

            //check permission
            if (auth()->user()->role === config('constants.employee')) {
                return 403;
            }

            //check user exist
            $user = $this->user->find($args['assign_to_users']);

            if ($user && isset($args['list']) && count($args['list']) > 0) {
                foreach ($args['list'] as $item) {
                    $condition = explode(",", $item);
                    $subscription = $this->subscription
                        ->where('assigned_to', null)
                        ->where('status', 0)
                        ->where('phone_number', $condition[0])
                        ->where('code', $condition[1])
                        ->update(['prior_user' => $user->id]);
                }
            } else {
                return 404;
            }
            \DB::commit();
            return $user;
        } catch (\Exception$e) {
            \DB::rollBack();
            return $e;
        }
    }
}
