<?php
namespace App\GraphQL\Mutations\ScanBalance;

use App\Models\ScanBalance;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UpdateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'updateNews',
        'description' => 'A mutation',
    ];

    protected $model;

    public function __construct(ScanBalance $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::type('ScanBalance');
    }

    public function args(): array
    {
        return [
            'id' => [
                'type' => Type::nonNull(Type::int()),
            ],
            'title' => [
                'type' => Type::string(),
            ],
            'content' => [
                'type' => Type::string(),
                'defaultValue' => '',
            ],
            'short_content' => [
                'type' => Type::string(),
                'defaultValue' => '',
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $model = $this->model->find($args['id']);
        $model->title = $args['title'];
        $model->content = $args['content'];
        $model->short_content = $args['short_content'];
        $model->save();

        return $model;
    }
}
