<?php
namespace App\GraphQL\Mutations\ExtendPack;

use App\Models\ExtendPack;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateExtendPack',
        'description' => 'A mutation',
    ];

    protected $extendPack;

    public function __construct(ExtendPack $extendPack)
    {
        $this->extendPack = $extendPack;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('ExtendPack');
    }

    public function args(): array
    {
        return [
            'code' => [
                'type' => Type::nonNull(Type::string()),
            ],
            'revenue' => [
                'type' => Type::int(),
            ],
            'real_revenue' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $extendPack = $this->extendPack->where('code', $args['code']);

        $extendPack->update([
            'revenue' => $args['revenue'],
            'real_revenue' => $args['real_revenue'],
        ]);

        return $extendPack->first();
    }
}
