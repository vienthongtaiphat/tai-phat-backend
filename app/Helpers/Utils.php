<?php
namespace App\Helpers;

class Utils
{
    public static function instance()
    {
        return new Utils();
    }

    public function trimPhoneNumber($phoneNumber)
    {
        $len = strlen($phoneNumber);
        if ($len < 10) {
            return "0" . $phoneNumber;
        } else if ($len > 10) {
            return trim($phoneNumber);
        }

        return $phoneNumber;
    }
}
