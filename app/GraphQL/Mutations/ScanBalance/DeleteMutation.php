<?php

namespace App\GraphQL\Mutations\ScanBalance;

use App\Models\ScanBalance;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class DeleteMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deleteNews',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(ScanBalance $model)
    {
        $this->model = $model;
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
        return $this->model->where('id', $args['id'])->delete();
    }
}
