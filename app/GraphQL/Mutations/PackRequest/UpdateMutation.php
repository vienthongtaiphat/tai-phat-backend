<?php
namespace App\GraphQL\Mutations\PackRequest;

use App\Models\Branch;
use App\Models\PackCodeRequest;
use App\Models\PackCodeStore;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updatePackRequest',
        'description' => 'A mutation',
    ];

    protected $packRequest;
    protected $packCodeStore;
    protected $branch;

    public function __construct(PackCodeRequest $packRequest, PackCodeStore $packCodeStore, Branch $branch)
    {
        $this->packRequest = $packRequest;
        $this->packCodeStore = $packCodeStore;
        $this->branch = $branch;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('PackCodeRequest');
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

            $packRequest = $this->packRequest->find($args['id']);

            $packRequest->status = $args['status'];
            $packRequest->approved_by = auth()->user()->id;
            $packRequest->save();

            $packCodeStore = $this->packCodeStore
                ->where('branch_id', $user->branch_id)
                ->where('pack_code', $packRequest->pack_code)
                ->first();

            if ($packCodeStore->amount <= 0) {
                $branch = $this->branch->find($user->branch_id);
                throw new \Exception('Hết số trong kho ' . $branch->display_name ?? $branch->name);
            }
            $packCodeStore->amount -= 1;
            $packCodeStore->save();

            \DB::commit();
            return $packRequest;
        } catch (\Exception$e) {
            \DB::rollBack();
            return $e;
        }
    }
}
