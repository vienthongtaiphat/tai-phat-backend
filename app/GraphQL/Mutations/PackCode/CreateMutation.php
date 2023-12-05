<?php

namespace App\GraphQL\Mutations\PackCode;

use App\Models\PackCodeHistory;
use App\Models\PackCodeStore;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $model;
    protected $packCodeStore;

    public function __construct(PackCodeHistory $model, PackCodeStore $packCodeStore)
    {
        $this->model = $model;
        $this->packCodeStore = $packCodeStore;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role <= config('constants.admin');
    }

    public function type(): Type
    {
        return GraphQL::type('PackCodeHistory');
    }

    public function args(): array
    {
        return [
            'pack_code' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'amount' => [
                'type' => Type::int(),
                'rules' => ['required'],
            ],
            'date' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $data = [
                'pack_code' => $args['pack_code'],
                'amount' => $args['amount'],
                'branch_id' => 0,
                'created_at' => isset($args['date']) ? $args['date'] : now(),
            ];
            $res = $this->model->create($data);

            // Lấy kho gốc của admin
            $packCodeStore = $this->packCodeStore->firstOrNew(
                [
                    'pack_code' => $args['pack_code'],
                    'branch_id' => 0,
                ]);

            //Cộng dồn amount vào kho gốc
            $packCodeStore->amount = $packCodeStore->amount + $args['amount'];
            $packCodeStore->save();
            \DB::commit();

            return $data;
        } catch (\Exception$e) {
            \DB::rollBack();
            throw new \Exception('Error !');
        }

    }
}
