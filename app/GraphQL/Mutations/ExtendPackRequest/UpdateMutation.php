<?php
namespace App\GraphQL\Mutations\ExtendPackRequest;

use App\Models\Branch;
use App\Models\ExtendPackRequest;
use App\Models\ExtendPackStore;
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
    protected $ExtendPackStore;
    protected $branch;

    public function __construct(ExtendPackRequest $packRequest, ExtendPackStore $ExtendPackStore, Branch $branch)
    {
        $this->packRequest = $packRequest;
        $this->ExtendPackStore = $ExtendPackStore;
        $this->branch = $branch;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('ExtendPackRequest');
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
            $packRequest->approved_by = $user->id;
            $packRequest->save();

            // $ExtendPackStore = $this->ExtendPackStore::firstOrNew(
            //     ['branch_id' => $user->branch_id],
            //     ['pack_code' => $packRequest->pack_code]
            // );

            // if ($ExtendPackStore->amount <= 0) {
            //     $branch = $this->branch->find($user->branch_id);
            //     throw new \Exception('Hết số trong kho ' . $branch->display_name ?? $branch->name);
            // }
            // $ExtendPackStore->amount -= 1;
            // $ExtendPackStore->save();

            \DB::commit();
            return $packRequest;
        } catch (\Exception$e) {
            \DB::rollBack();
            return $e;
        }
    }
}
