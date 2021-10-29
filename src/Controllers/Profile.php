<?php

namespace App\Controllers;

use App\Dictionary;
use App\Templates\Keyboard;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class Profile
{
    public function __invoke(int $id, Client $bot): void
    {
        /** @var Message $message */
        $message = $bot->sendPhoto($id, new \CURLFile(Dictionary::config()->get('banner')), "Вот ваш 🆔ID передайте его 👨‍💻менеджеру:
<b>$id</b>", null, new Keyboard(), false, 'html');
        $_SERVER['messageId'] = $message->getMessageId();
    }
}
