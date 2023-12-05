<?php
namespace App\GraphQL\Mutations\PackCode;

use App\Models\PackCodeHistory;
use App\Models\PackCodeStore;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $history;
    protected $packCodeStore;

    public function __construct(PackCodeHistory $history, PackCodeStore $packCodeStore)
    {
        $this->history = $history;
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
                'type' => Type::string(),
            ],
            'branch_id' => [
                'type' => Type::int(),
            ],
            'amount' => [
                'type' => Type::int(),
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
            $packStore = $this->packCodeStore->where('pack_code', $args['pack_code'])
                ->where('branch_id', 0)
                ->first();

            if ($args['amount'] <= $packStore->amount) {

                //Cập nhật kho gốc của admin
                $packStore->amount = $packStore->amount - $args['amount'];
                $packStore->save();

                //Tạo mới lịch sử gói cho chi nhánh
                $this->history->create([
                    'pack_code' => $args['pack_code'],
                    'amount' => $args['amount'],
                    'branch_id' => $args['branch_id'],
                    'created_at' => isset($args['date']) ? $args['date'] : now(),
                ]);

                //Tạo mới lịch sử gói cho admin
                // $this->history->create([
                //     'pack_code' => $args['pack_code'],
                //     'amount' => $args['amount'] * -1,
                //     'branch_id' => 0,
                // ]);

                //Cập nhật kho gói của chi nhánh
                $branchStore = $this->packCodeStore->firstOrNew(
                    [
                        'pack_code' => $args['pack_code'],
                        'branch_id' => $args['branch_id'],
                    ]);

                $branchStore->amount = $branchStore->amount + $args['amount'];
                $branchStore->save();

                \DB::commit();

                return $packStore;
            }
            throw new \Exception('Số lượng mã không đủ !');
        } catch (\Exception$e) {
            \DB::rollBack();
            throw new \Exception($e->getMessage());
        }
    }
}
