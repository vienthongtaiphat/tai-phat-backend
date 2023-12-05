<?php

namespace App\GraphQL\Mutations\ExtendPackRequest;

use App\Models\Branch;
use App\Models\ExtendPack;
use App\Models\ExtendPackRequest;
use App\Models\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createExtendPackRequest',
        'description' => 'Pack mutation',
    ];

    protected $extendPackRequest;

    public function __construct(ExtendPackRequest $extendPackRequest)
    {
        $this->extendPackRequest = $extendPackRequest;
    }

    public function type(): Type
    {
        return GraphQL::type('ExtendPackRequest');
    }

    public function args(): array
    {
        return [
            'pack_code' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'phone_number' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        $user = auth()->user();

        $phoneNumber = \App\Helpers\Utils::instance()->trimPhoneNumber($args['phone_number']);
        $packCode = trim($args['pack_code']);

        $extend = $this->extendPackRequest->where(
            [
                'pack_code' => $packCode,
                'phone_number' => $phoneNumber,
            ])
            ->where('created_at', '>=', now()->subDays(30)->endOfDay())
            ->first();

        if (!$extend) {
            $pack = ExtendPack::where('code', $packCode)->first();
            $res = $this->extendPackRequest->create(
                [
                    'pack_code' => $packCode,
                    'phone_number' => $phoneNumber,
                    'revenue' => $pack->revenue,
                    'real_revenue' => $pack->real_revenue,
                    'created_by' => $user->id,
                ]);

            $branch = Branch::find($user->branch_id);
            $message = "Gia hạn gói: " . $packCode .
            "\nSĐT: " . $phoneNumber .
            "\nNgười tạo: " . $user->name .
                "\nGH " . $packCode . " " . $phoneNumber;
            if ($branch?->channel_id) {
                \App\Helpers\SendTelegram::instance()->sendMessage($branch?->channel_id, $message);
            }
            if ($branch?->id === 19) {
                \App\Helpers\SendTelegram::instance()->sendMessage("-800825533", $message); // nhóm Phong
            }
            return $res;
        }

        $user = User::find($extend->created_by);
        throw new \Exception ('Số điện thoại đã được tạo bởi  ' . $user->name);
    }
}
