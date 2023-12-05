<?php

namespace App\GraphQL\Queries\CallHistory;

use App\Models\CallHistory;
use Carbon\Carbon;
use Closure;
use GraphQL;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Query;

class ListQuery extends Query
{
    protected $callHistory;

    protected $attributes = [
        'name' => 'CallHistory',
    ];

    public function __construct(CallHistory $callHistory)
    {
        $this->callHistory = $callHistory;
    }

    public function type(): Type
    {
        return Type::listOf(GraphQL::type('CallHistory'));
    }

    public function args(): array
    {
        return [
            'limit' => [
                'type' => Type::int(),
                'default' => 50,
            ],
            'page' => [
                'type' => Type::int(),
                'default' => 1,
            ],
            'from_date' => [
                'type' => Type::string(),
            ],
            'to_date' => [
                'type' => Type::string(),
            ],
            'branch_id' => [
                'type' => Type::int(),
                'default' => null,
            ],
            'user_id' => [
                'type' => Type::int(),
                'default' => null,
            ],
        ];
    }

    public function resolve($root, array $args, $context, ResolveInfo $info, Closure $getSelectFields)
    {
        $query = $this->callHistory->scopeSearch($args);
        foreach ($query as $key => $row) {
            $sumFailCall = CallHistory::where('accountcode', $row['accountcode'])
                ->whereDate('created_at', Carbon::parse($row['created_at'])->format('Y-m-d'))
                ->where('disposition', '<>', 1)
                ->count();

            $query[$key]['disposition'] = $sumFailCall;
        }

        return $query;
    }
}
