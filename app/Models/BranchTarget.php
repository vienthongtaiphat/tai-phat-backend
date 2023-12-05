<?php

namespace App\Models;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BranchTarget extends Model
{
    use HasFactory;

    public $timestamps = true;

    protected $fillable = [
        "branch_id",
        "target",
        "month",
        "year",
    ];

    public function branch()
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    public function scopeSearch($filters = [])
    {
        $user = auth()->user();

        $query = $this;

        if (isset($filters['from_date']) && $filters['from_date'] !== '') {
            $query = $query->whereDate('created_at', '>=', $filters['from_date']);
        }

        if (isset($filters['to_date']) && $filters['to_date'] !== '') {
            $query = $query->whereDate('created_at', '<=', $filters['to_date']);
        }

        return $query->orderBy('id', 'desc');
    }
}
