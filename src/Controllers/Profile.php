<?php

namespace App\Controllers;

use App\Dictionary;
use App\Templates\Keyboard;
use TelegramBot\Api\Client;

class Profile
{
    public function __invoke(int $id, Client $bot): void
    {
        $bot->sendPhoto($id, new \CURLFile(Dictionary::config()->get('banner')), "Вот ваш 🆔ID передайте его 👨‍💻менеджеру:
<b>$id</b>", null, new Keyboard(), false, 'html');
    }
}
