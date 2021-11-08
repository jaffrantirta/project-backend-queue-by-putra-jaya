<?php
namespace App\Util;
use App\Models\Order_status;
use App\Models\Queue_number;
use App\Models\Shop_user;
use App\Models\Status;
use Carbon\Carbon;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class Statistic {
    public static function weekly($week, $year)
    {
        // $date = Carbon::now();
        // $date->setISODate(Carbon::now()->dayOfYear, 2021);
        // $data['start'] = $date->startOfWeek();
        // $data['end'] = $date->endOfWeek();
        // return $data;
        $dto = new DateTime();
        $dto->setISODate($year, $week);
        $ret['week_start'] = $dto->format('Y-m-d');
        $dto->modify('+6 days');
        $ret['week_end'] = $dto->format('Y-m-d');
        return $ret;
    }
}