<?php

namespace App\Controllers;

use App\Dictionary;
use App\Templates\Keyboard;
use TelegramBot\Api\Client;

class Index
{
    public function __invoke(int $id, Client $bot): void
    {
        $bot->sendPhoto($id, new \CURLFile(Dictionary::config()->get('banner')), "🖐 Добрый день!
Для начала работы с ботом Вам необходимо передать менеджеру свой 🆔ID.
‼️Он нужен для определения ваших 📱PWA прил.
Вот ваш ID передайте его 👨‍💻менеджеру:
<b>$id</b>", null, new Keyboard(), false, 'html');
    }
}
