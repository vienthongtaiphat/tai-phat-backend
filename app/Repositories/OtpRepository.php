<?php namespace App\Repositories;

use App\Models\Otp;
use App\Interfaces\OtpRepositoryInterface;
use App\Repositories\BaseRepository;

class OtpRepository extends BaseRepository implements OtpRepositoryInterface
{
    public function __toString()
    {return 'OtpRepository';}

    public function getModel()
    {
        return Otp::class;
    }
}
