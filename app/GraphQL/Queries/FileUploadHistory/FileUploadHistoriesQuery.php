<?php

namespace App\GraphQL\Queries\FileUploadHistory;

use App\GraphQL\ArrayToPaginate;
use App\Models\FileUploadedHistory;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class FileUploadHistoriesQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'FileUploadHistories',
    ];

    public function __construct(FileUploadedHistory $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {

        return GraphQL::paginate('FileUploadedHistory');
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::int(),
                'default' => 6,
            ],
            'page' => [
                'type' => Type::int(),
                'default' => 1,
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $user = auth()->user();

        if ($user->role === config('constants.manager') || $user->role === config('constants.employee')) {
            $this->model = $this->model->where('upload_by', $user->id);
        }

        $this->model = $this->model->orderBy('id', 'desc');
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($this->model->get()->toArray()) : $this->model->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}
