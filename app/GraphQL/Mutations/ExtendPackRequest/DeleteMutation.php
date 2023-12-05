<?php
namespace App\GraphQL\Mutations\ExtendPackRequest;

use App\Models\ExtendPackRequest;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deletePackRequest',
        'description' => 'A mutation',
    ];

    protected $packRequest;

    public function __construct(ExtendPackRequest $packRequest)
    {
        $this->packRequest = $packRequest;
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
        $res = $this->packRequest->whereIn('id', $args['ids']);
        return $res->delete();
    }
}
