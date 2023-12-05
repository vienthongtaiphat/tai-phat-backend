<?php

declare (strict_types = 1);

namespace App\GraphQL\Mutations\FileUploadedHistory;

use App\Models\Assign;
use App\Models\FileUploadedHistory;
use App\Models\Subscription;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteFileUploadedMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteFileUploaded',
        'description' => 'A mutation',
    ];

    protected $model;
    protected $subscription;
    protected $assign;

    public function __construct(FileUploadedHistory $model, Subscription $subscription, Assign $assign)
    {
        $this->subscription = $subscription;
        $this->assign = $assign;
        $this->model = $model;
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
            'id' => [
                'type' => Type::int(),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        // Remove file
        if ($this->model->find($args['id'])->delete()) {
            // Remove assign
            $this->assign->where('file_id', $args['id'])->delete();
            // Remove subscription
            $this->subscription->where('file_id', $args['id'])->delete();
        }
        return true;
    }
}
