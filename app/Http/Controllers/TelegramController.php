<?php
namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function updatedActivity()
    {
        // $channelId = "1565150325";
        // $response = Telegram::sendMessage([
        //     'chat_id' => $channelId,
        //     'text' => 'Hello World',
        // ]);
        // $messageId = $response->getMessageId();

        $activity = Telegram::getUpdates();
        dd($activity);

        return true;
    }

    public function simPushTelegram(Request $request)
    {
        $customerName = $request->get('customerName', null);
        $refCode = $request->get('refCode', null);
        $customerPhone = $request->get('customerPhone', null);
        $buySim = $request->get('buySim', null);
        $requiredCode = $request->get('requiredCode', null);
        $simType = $request->get('simType', null);
        $address = $request->get('address', null);
        $supplierName = $request->get('supplierName', '');
        $simPrice = $request->get('simPrice', '');

        $user = $refCode ? User::where('user_code', $refCode)->first() : null;
        $name = $refCode ? $user?->name : '';
        $message = "Nhân viên: " . $name .
            "\nID: " . $refCode .
            "\nTên khách: " . $customerName .
            "\nSĐT khách: " . $customerPhone .
            "\nLoại sim: " . $simType .
            "\nSố mua: " . $buySim .
            "\nGiá: " . $simPrice .
            "\nKho: " . $supplierName .
            "\nGói cước bắt buộc: " . $requiredCode .
            "\nĐịa chỉ: " . $address;

        $messageForBranch = "Nhân viên: " . $name .
            "\nID: " . $refCode .
            "\nTên khách: " . $customerName .
            "\nLoại sim: " . $simType .
            "\nSố mua: " . $buySim .
            "\nGói cước bắt buộc: " . $requiredCode .
            "\nĐịa chỉ: " . $address;

        $channel = "-1001944820758";
        \App\Helpers\SendTelegram::instance()->sendMessage($channel, $message);
        if ($user->branch_id) {
            $branch = Branch::find($user->branch_id);
            \App\Helpers\SendTelegram::instance()->sendMessage($branch?->sim_channel_id, $messageForBranch);
        }

        return response()->json(true);
    }
}
