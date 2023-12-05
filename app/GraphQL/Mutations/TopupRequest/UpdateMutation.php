<?php
namespace App\GraphQL\Mutations\TopupRequest;

use App\Models\Branch;
use App\Models\TopupRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updatePayDebtRequest',
        'description' => 'A mutation',
    ];

    protected $topupRequest;

    public function __construct(TopupRequest $topupRequest)
    {
        $this->topupRequest = $topupRequest;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('TopupRequest');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'status' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $user = auth()->user();

            $topupRequest = $this->topupRequest->find($args['id']);
            $topupRequest->status = $args['status'];
            if ($args['status'] === 2) {
                $topupRequest->approved_by = $user->id;
            }
            $topupRequest->save();

            \DB::commit();
            return $topupRequest;
        } catch (\Exception$e) {
            \DB::rollBack();
            return $e;
        }
    }
}
