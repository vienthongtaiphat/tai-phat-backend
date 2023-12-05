<?php
namespace App\GraphQL\Mutations\PayDebtRequest;

use App\Models\PayDebtRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updatePayDebtRequest',
        'description' => 'A mutation',
    ];

    protected $payDebtRequest;

    public function __construct(PayDebtRequest $payDebtRequest)
    {
        $this->payDebtRequest = $payDebtRequest;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('PayDebtRequest');
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

            $payDebtRequest = $this->payDebtRequest->find($args['id']);

            $payDebtRequest->status = $args['status'];
            $payDebtRequest->approved_by = auth()->user()->id;
            $payDebtRequest->save();

            \DB::commit();
            return $payDebtRequest;
        } catch (\Exception$e) {
            \DB::rollBack();
            return $e;
        }
    }
}
