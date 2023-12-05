<?php

namespace App\GraphQL\Mutations\ExtendPack;

use App\Models\ExtendPack;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteExtendPack',
        'description' => 'A mutation',
    ];

    protected $extendPack;

    public function __construct(ExtendPack $extendPack)
    {
        $this->extendPack = $extendPack;
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
            'code' => [
                'type' => Type::string(),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        return $this->extendPack->where('code', $args['code'])->delete();
    }
}
