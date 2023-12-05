<?php

namespace App\Models;

use App\Http\Controllers\Admin\ReportController;
use App\Models\DichVu24hAccount;
use App\Models\RefundHistory;
use App\Models\UpgradeHistory;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRevenue extends Model
{
    use HasFactory;

    public $timestamps = true;
    protected $table = 'month_revenues';
    protected $fillable = [
        "user_id",
        "month",
        "day",
        "year",
        "revenue",
        "created_at",
    ];

    public static function getUserRevenue()
    {
        $month = Carbon::now()->month;
        $year = Carbon::now()->year;

        UserRevenue::where([
            'month' => $month,
            'year' => $year,
        ])->delete();

        $from_date = Carbon::now()->startOfMonth();
        $to_date = Carbon::now()->endOfMonth();
        $branch_id = 999;
        $user_id = null;
        $register_channel = null;

        $model = new ReportController(new DichVu24hAccount, new User, new RefundHistory, new UpgradeHistory);
        $data = $model->getTotalRevenue($from_date,
            $to_date,
            $branch_id,
            $user_id,
            null,
            $register_channel);

        foreach ($data as $item) {
            $today = Carbon::parse($item['created_at']);
            UserRevenue::create(
                [
                    'user_id' => $item['user_id'],
                    'day' => $today->day,
                    'month' => $today->month,
                    'year' => $today->year,
                    'revenue' => $item['revenue'],
                ]);
        }

        return $data;
    }
}
