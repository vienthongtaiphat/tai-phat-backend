<?php namespace App\Repositories;

use App\Interfaces\CtvUserRepositoryInterface;
use App\Models\CtvUser;
use App\Repositories\BaseRepository;

class CtvUserRepository extends BaseRepository implements CtvUserRepositoryInterface
{
    public function __toString()
    {return 'CtvUserRepository';}

    public function getModel()
    {
        return CtvUser::class;
    }
}
