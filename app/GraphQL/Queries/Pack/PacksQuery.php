<?php

namespace App\GraphQL\Queries\Pack;

use App\GraphQL\ArrayToPaginate;
use App\Models\Pack;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class PacksQuery extends Query
{
    protected $model;

    protected $attributes = [
        'name' => 'Packs',
    ];

    public function __construct(Pack $model)
    {
        $this->model = $model;
    }

    public function type(): Type
    {
        return GraphQL::paginate('Pack');
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::int(),
                'default' => 0,
            ],
            'page' => [
                'type' => Type::int(),
                'default' => 1,
            ],
            'search' => [
                'type' => Type::string(),
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $query = $this->model;
        if (isset($args['search']) && $args['search'] !== '') {
            $searchUppercase = strtoupper($args['search']);
            $searchLowercase = strtolower($args['search']);

            $query = $query->where('code', 'LIKE', '%' . $searchUppercase . '%')
                ->where('code', 'LIKE', '%' . $searchLowercase . '%');
        }
        $query = $query->orderBy('code', 'asc');
        return $args['limit'] === 0 ? ArrayToPaginate::paginate($query->get()->toArray()) : $query->paginate($args['limit'], ['*'], 'page', $args['page']);
    }
}