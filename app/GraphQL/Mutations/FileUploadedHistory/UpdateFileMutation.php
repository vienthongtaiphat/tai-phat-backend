<?php
namespace App\GraphQL\Mutations\FileUploadedHistory;

use App\Models\FileUploadedHistory;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateFileMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateFileUploadedHistory',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(FileUploadedHistory $model)
    {
        $this->model = $model;
    }

    public function authorize($root, array $args, $ctx, $resolveInfo = null, $getSelectFields = null): bool
    {
        return auth()->user()->role < config('constants.employee');
    }

    public function type(): Type
    {
        return GraphQL::type('FileUploadedHistory');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'activated' => [
                'type' => Type::int(),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $model = $this->model->findOrFail($args['id']);
        $model->fill($args);
        $model->save();

        return $model;
    }
}
