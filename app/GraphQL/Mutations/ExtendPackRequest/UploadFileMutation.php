<?php
namespace App\GraphQL\Mutations\ExtendPackRequest;

use App\Models\ExtendPackRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class UploadFileMutation extends Mutation
{
    protected $attributes = [
        'name' => 'deletePackRequest',
        'description' => 'A mutation',
    ];

    protected $packRequest;

    public function __construct(ExtendPackRequest $packRequest)
    {
        $this->packRequest = $packRequest;
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
            'successData' => [
                'type' => Type::listOf(GraphQL::type('UpgradeHistory')),
            ],
            'errorData' => [
                'type' => Type::listOf(GraphQL::type('UpgradeHistory')),
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            $userId = auth()->user()->id;
            $successData = $args['successData'];
            $errorData = $args['errorData'];

            foreach ($successData as $item) {
                $code = $item["code"];
                $created_at = $item["created_at"];

                $this->packRequest->where('phone_number', $item["phone_number"])
                    ->where(function ($query) use ($code) {
                        $query->where('pack_code', strtoupper($code))
                            ->orWhere('pack_code', strtolower($code));
                    })
                    ->update([
                        'status' => 2,
                        'approved_by' => $userId,
                        'created_at' => $created_at,
                    ]);
            }

            foreach ($errorData as $item) {
                $code = $item["code"];
                $created_at = $item["created_at"];

                $this->packRequest->where('phone_number', $item["phone_number"])
                    ->where(function ($query) use ($code) {
                        $query->where('pack_code', strtoupper($code))
                            ->orWhere('pack_code', strtolower($code));
                    })
                    ->update([
                        'status' => 3,
                        'approved_by' => $userId,
                        'created_at' => $created_at,
                    ]);
            }
            return true;
        } catch (\Exception $e) {
            throw new \Exception ($e->getMessage());
        }
    }
}
