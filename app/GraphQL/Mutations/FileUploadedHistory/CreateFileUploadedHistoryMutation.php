<?php

namespace App\GraphQL\Mutations\FileUploadedHistory;

use App\Models\Branch;
use App\Models\FileUploadedHistory;
use App\Models\Subscription;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateFileUploadedHistoryMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createFileUploadedHistory',
        'description' => 'FileUploadedHistoryMutation',
    ];

    protected $model;
    protected $subscription;
    protected $branch;

    public function __construct(FileUploadedHistory $model, Subscription $subscription, Branch $branch)
    {
        $this->model = $model;
        $this->subscription = $subscription;
        $this->branch = $branch;
    }

    public function type(): Type
    {
        return GraphQL::type('FileUploadedHistory');
    }

    public function args(): array
    {
        return [
            'file_name' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'list' => [
                'type' => Type::nonNull(GraphQL::type('[Subscription]')),
                'rules' => ['required'],
            ],
            'divide_rate' => [
                'type' => GraphQL::type('[BranchInputType]'),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $userId = auth()->user()->id;

            $duplicateDatas = Subscription::validateDuplicate($args['list']);

            if (!is_null($duplicateDatas) && count($duplicateDatas) > 0) {
                return (object) ['id' => null, 'duplicateDatas' => $duplicateDatas];
            } else {
                $res = $this->model->create([
                    'file_name' => $args['file_name'],
                    'upload_by' => $userId,
                ]);

                if ($res) {
                    $listCount = count($args['list']);

                    if (isset($args['divide_rate'])) {
                        //tính toán block size và offset
                        $offsets = [];
                        $start = 0;
                        $ratio = 0;

                        foreach ($args['divide_rate'] as $t) {
                            $ratio += $t['rate'];
                            $ratio = $ratio > 100 ? 100 : $ratio;
                            $end = ceil(($listCount * $ratio) / 100);
                            array_push($offsets, ['branch_id' => $t['id'], 'start' => $start, 'end' => $end]);

                            //Thêm vào DB
                            $subs = array_slice($args['list'], $start, $end - $start);
                            foreach ($subs as $pack) {
                                $branchId = $t['id'];

                                $this->subscription->create([
                                    'phone_number' => \App\Helpers\Utils::instance()->trimPhoneNumber($pack['phone_number']),
                                    'code' => $pack['code'],
                                    'first_register_date' => $pack['first_register_date'],
                                    'first_expired_date' => $pack['expired_date'],
                                    'phone_type' => $pack['phone_type'],
                                    'file_id' => $res->id,
                                    'upload_by' => $userId,
                                    'branch_id' => $branchId]);
                            }

                            $start = $end;
                        }
                    } else {
                        $branches = $this->branch->pluck('id')->toArray(); //lấy danh sách branch
                        $countBranches = count($branches);
                        $blockSize = ceil($listCount / $countBranches * 1.0);

                        foreach ($args['list'] as $key => $pack) {
                            $branchId = ceil($key / $blockSize) - 1;
                            if ($branchId >= $countBranches) {
                                $branchId = $countBranches - 1;
                            } else if ($branchId < 0) {
                                $branchId = 0;
                            }
                            $branchId = $branches[$branchId];

                            $this->subscription->create([
                                'phone_number' => \App\Helpers\Utils::instance()->trimPhoneNumber($pack['phone_number']),
                                'code' => $pack['code'],
                                'first_register_date' => $pack['first_register_date'],
                                'first_expired_date' => $pack['expired_date'],
                                'phone_type' => $pack['phone_type'],
                                'file_id' => $res->id,
                                'branch_id' => $branchId]);
                        }
                    }
                    \DB::commit();
                    $res->duplicateDatas = [];

                    return $res;
                }

                \DB::rollBack();
                throw new Exception('Not found !');
            }
        } catch (\Exception $e) {
            \DB::rollBack();
            return $e;
        }
    }
}
