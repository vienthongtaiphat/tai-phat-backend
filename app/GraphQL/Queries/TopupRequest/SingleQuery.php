<?php

namespace App\GraphQL\Queries\TopupRequest;

use App\Models\TopupRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class SingleQuery extends Query
{
    protected $attributes = [
        'name' => 'TopupRequest',
    ];

    protected $topupRequest;

    public function __construct(TopupRequest $topupRequest)
    {
        $this->topupRequest = $topupRequest;
    }

    public function type(): Type
    {
        return GraphQL::type('TopupRequest');
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
        ];
    }

    public function resolve($root, $args)
    {
        $data = $this->topupRequest;
        // if (isset($args['pack_code'])) {
        //     $data = $data->where('pack_code', $args['pack_code']);
        // }

        // if (isset($args['branch_id'])) {
        //     $data = $data->where('branch_id', $args['branch_id']);
        // }
        return $data->first();
    }
}