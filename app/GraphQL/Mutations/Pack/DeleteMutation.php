<?php

namespace App\GraphQL\Mutations\Pack;

use App\Models\Pack;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteExtendPack',
        'description' => 'A mutation',
    ];

    protected $pack;

    public function __construct(Pack $pack)
    {
        $this->pack = $pack;
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
        return $this->pack->where('code', $args['code'])->delete();
    }
}