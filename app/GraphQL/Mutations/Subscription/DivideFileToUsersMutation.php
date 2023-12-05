<?php

namespace App\GraphQL\Mutations\Subscription;

use App\Models\Assign;
use App\Models\FileUploadedHistory;
use App\Models\User;
use GraphQL;
use GraphQL\Type\Definition\Type;
use Illuminate\Validation\ValidationException;
use Rebing\GraphQL\Support\Mutation;

class DivideFileToUsersMutation extends Mutation
{
    protected $attributes = [
        'name' => 'DivideFileToUsersMutation',
        'description' => 'DivideFileToUsersMutation',
    ];

    protected $user;
    protected $fileUploadedHistory;
    protected $assign;

    public function __construct(User $user, FileUploadedHistory $fileUploadedHistory, Assign $assign)
    {
        $this->user = $user;
        $this->fileUploadedHistory = $fileUploadedHistory;
        $this->assign = $assign;
    }

    public function type(): Type
    {
        return GraphQL::type('User');
    }

    public function args(): array
    {
        return [
            'file_id' => [
                'type' => Type::nonNull(Type::int()),
                'rules' => ['required'],
            ],
            'list' => [
                'type' => Type::listOf(Type::int()),
                'rules' => ['required'],
            ],
        ];
    }

    public function resolve($root, $args)
    {
        try {
            \DB::beginTransaction();
            $user = auth()->user();

            //check permission
            if ($user->role === config('constants.employee')) {
                throw ValidationException::withMessages(['User' => 'Permission denied !']);
            }

            $file = $this->fileUploadedHistory->find($args['file_id']);
            $totalUsers = count($args['list']);
            if ($file && $totalUsers > 0) {

                $this->assign->where('file_id', $file->id)->delete();
                foreach ($args['list'] as $item) {
                    $user = $this->user->find($item);
                    if ($user) {
                        $assign = $this->assign->firstOrCreate([
                            'user_id' => $user->id,
                            'file_id' => $file->id,
                        ]);
                    }
                }
            } else {
                throw ValidationException::withMessages(['File' => 'File not found or total user is zero!']);
            }
            \DB::commit();
            return $file;
        } catch (\Exception$e) {
            \DB::rollBack();
            return $e;
        }
    }

    public static function array_pluck($arr, $toPluck)
    {
        $ret = array();
        foreach ($arr as $item) {
            array_push($ret, $item[$toPluck]);
        }
        return $ret;
    }
}
