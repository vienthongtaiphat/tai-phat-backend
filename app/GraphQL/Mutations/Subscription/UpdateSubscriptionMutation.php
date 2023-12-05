<?php

declare (strict_types = 1);

namespace App\GraphQL\Mutations\Subscription;

use App\Models\Subscription;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateSubscriptionMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateSubscription',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(Subscription $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('SubscriptionQuery');
    }

    public function args(): array
    {
        return [
            'phone_number' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'code' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'status' => [
                'type' => Type::int(),
            ],
            'user_note' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $user = auth()->user();
        $sub = $this->model
            ->where('phone_number', \App\Helpers\Utils::instance()->trimPhoneNumber($args['phone_number']))
            ->where('code', $args['code']);

        // if (auth()->user()->role !== config('constants.employee')) {
        //     $sub = $sub->where('assigned_to', $args['assigned_to']);
        // }

        $userNote = '';
        if (isset($args['user_note'])) {
            $userNote = $args['user_note'];
        }

        $data = $sub->first();
        $sub->update(['assigned_to' => $user->id, 'status' => $args['status'], 'user_note' => $userNote]);
        return $data;
    }
}
