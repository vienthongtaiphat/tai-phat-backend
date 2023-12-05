<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

Route::get('/', function () {
    return 'Server OK';
});

Route::get('/test', function (Request $request) {
    $activity = Telegram::getUpdates();
    dd($activity);
});

Route::get('/send-tele', function (Request $request) {
    $channelId = "-800825533";
    $response = Telegram::sendMessage([
        'chat_id' => $channelId,
        'text' => '1111',
    ]);
    $messageId = $response->getMessageId();
    return $messageId;
});

Route::get('/clear-cache', function (Request $request) {
    \Artisan::call('cache:clear');
    \Artisan::call('route:clear');
    \Artisan::call('config:cache');

    echo 'Clear cache thành công !';
});
