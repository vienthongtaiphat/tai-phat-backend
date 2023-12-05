<?php

namespace App\GraphQL\Queries\Subscription;

use App\Models\Assign;
use App\Models\DataLog;
use App\Models\Subscription;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SubscriptionQuery extends Query
{
    protected $attributes = [
        'name' => 'Subscription',
    ];

    protected $model;
    protected $assign;
    protected $dataLog;

    public function __construct(Subscription $model, Assign $assign, DataLog $dataLog)
    {
        $this->model = $model;
        $this->assign = $assign;
        $this->dataLog = $dataLog;
    }

    public function type(): Type
    {
        return GraphQL::type('SubscriptionQuery');
    }

    public function args(): array
    {
        return [
            'phone_number' => [
                'type' => Type::string(),
            ],
            'code' => [
                'type' => Type::string(),
            ],
            'isRandom' => [
                'type' => Type::boolean(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $user = auth()->user();
        $userId = $user->id;

        if (isset($args['phone_number']) && isset($args['code'])) {
            $data = $this->model->where('phone_number', $args['phone_number'])
                ->where('code', $args['code'])
                ->first();
        } else if (isset($args['isRandom'])) {
            $userBranch = $user->branch_id;

            //Lấy danh sách file id đã được assign cho nhân viên
            $assignedFileIds = $this->assign
                ->leftJoin('file_uploaded_histories', 'file_uploaded_histories.id', '=', 'assigns.file_id')
                ->where('user_id', $userId)
                ->where('file_uploaded_histories.activated', 1)
                ->pluck('file_id')
                ->toArray();

            //Lấy số thuê bao được phân cho nhân viên
            $data = $this->model->whereIn('file_id', $assignedFileIds)
                ->where('status', 0)
                ->inRandomOrder()
                ->lockForUpdate()
                ->first();

            //Assign thuê bao cho nhân viên
            if ($data) {
                $data->status = 7;
                $data->assigned_to = $userId;
                $data->assigned_date = now();
                $data->save();

                $log = $this->dataLog->createLog($data->phone_number);
            }
        }

        return $data;
    }
}
