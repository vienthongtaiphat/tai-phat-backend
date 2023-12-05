<?php

namespace App\GraphQL\Mutations\PackRequest;

use App\Models\Branch;
use App\Models\PackCodeRequest;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Rebing\GraphQL\Support\Mutation;

class CreateMutation extends Mutation
{
    protected $attributes = [
        'name' => 'createPackRequest',
        'description' => 'Pack mutation',
    ];

    protected $packRequest;

    public function __construct(PackCodeRequest $packRequest)
    {
        $this->packRequest = $packRequest;
    }

    public function type(): Type
    {
        return GraphQL::type('PackCodeRequest');
    }

    public function args(): array
    {
        return [
            'pack_code' => [
                'type' => Type::nonNull(Type::string()),
                'rules' => ['required', 'string'],
            ],
            'type' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
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
        if (!$this->packRequest->where(
            [
                'pack_code' => $args['pack_code'],
                'type' => $args['type'],
                'phone_number' => $phoneNumber,
            ])->exists()) {
            $res = $this->packRequest->create(
                [
                    'pack_code' => $args['pack_code'],
                    'type' => $args['type'],
                    'phone_number' => $phoneNumber,
                    'created_by' => $user->id,
                ]);

            $branch = Branch::find($user->branch_id);
            $type = $args['type'] === 1 ? 'nâng cấp' : 'gia hạn';
            $message = "Duyệt code: " . $args['pack_code'] . " " . $type .
            "\nSĐT: " . $phoneNumber .
            "\nNgười tạo: " . $user->name .
                "\nDKT " . $args['pack_code'] . " " . $phoneNumber;

            if ($branch?->channel_id) {
                \App\Helpers\SendTelegram::instance()->sendMessage($branch->channel_id, $message);
            }
            if ($branch->id === 19) {
                \App\Helpers\SendTelegram::instance()->sendMessage("-800825533", $message); // nhóm Phong
            }
            return $res;
        }

        throw new \Exception ('Số điện thoại đã tồn tại');
    }
}
