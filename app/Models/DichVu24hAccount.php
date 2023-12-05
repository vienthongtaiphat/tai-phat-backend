<?php

namespace App\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DichVu24hAccount extends Model
{
    use HasFactory;
    protected $table = 'dichvu24h_accounts';

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }
}
