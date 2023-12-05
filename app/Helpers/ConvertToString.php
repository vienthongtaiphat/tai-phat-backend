<?php
namespace App\Helpers;
use DateTime;

class ConvertToString
{
    public static function instance()
    {
        return new ConvertToString();
    }

    public function refundStatus($status)
    {
        $return_value = match($status) {
            0 => 'Đã nhận, chờ xử lý',
            1 => 'Đã nhận, đang xử lý',
            2 => 'Hoàn thành',
            3 => 'Hoàn tiền',
            91 => 'Vượt quá số tiền nhận tối đa trong ngày',
            92 => 'Độ dài của số điện thoại không đúng (9 / 10 / 11)',
            93 => 'Sai mệnh giá hệ thống quy định',
            94 => 'Thông tin nhà mạng không đúng (VIETTEL, MOBI, VINA)',
            95 => 'Đang tạm dừng dịch vụ cho nhà mạng này',
            96 => 'Không được gửi đơn liên tiếp cùng một số',
            97 => 'Số dư không đủ để thực hiện giao dịch',
            98 => 'Giao dịch không thành công',
            99 => 'Giao dịch bị từ chối',
        default=> ''
        };

        return $return_value;
    }

    public function packCodeType($status)
    {
        $return_value = match($status) {
            1 => 'Nâng cấp',
            2 => 'Gia hạn',
        default=> ''
        };

        return $return_value;
    }

    public function callStatus($status)
    {
        $return_value = match($status) {
            0 => 'Chưa lấy số',
            1 => 'Thành công',
            2 => 'Không nghe máy',
            3 => 'Thuê bao',
            4 => 'KH từ chối',
            5 => 'Khách hẹn',
            6 => 'Khách tham khảo',
            7 => 'Đã lấy số',
        default=> ''
        };

        return $return_value;
    }

    public function roles($r)
    {
        $return_value = match($r) {
            0 => 'Admin',
            1 => 'Quản trị hệ thống',
            2 => 'Quản lý chi nhánh',
            3 => 'Nhân viên',
        default=> ''
        };

        return $return_value;
    }

    public function userTypes($r)
    {
        $return_value = match($r) {
            1 => 'Full time',
            2 => 'Part time',
        default=> ''
        };

        return $return_value;
    }

    public function validateDate($date, $format = 'Y-m-d')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date ? true : false;
    }
}