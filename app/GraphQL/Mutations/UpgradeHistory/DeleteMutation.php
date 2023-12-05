<?php

namespace App\GraphQL\Mutations\UpgradeHistory;

use App\Models\UpgradeHistory;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteUpgradeHistory',
        'description' => 'A mutation',
    ];

    protected $upgradeHistory;

    public function __construct(UpgradeHistory $upgradeHistory)
    {
        $this->upgradeHistory = $upgradeHistory;
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
        return $this->upgradeHistory->find($args['id'])->delete();
    }
}
