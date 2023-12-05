<?php
namespace App\Helpers;

use Telegram\Bot\Laravel\Facades\Telegram;

class SendTelegram
{
    public static function instance()
    {
        return new SendTelegram();
    }

    public function sendMessage($channelId, $message)
    {
        try {
            if ($channelId) {
                $response = Telegram::sendMessage([
                    'chat_id' => $channelId,
                    'text' => $message,
                ]);

                $messageId = $response->getMessageId();
                return $messageId;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
