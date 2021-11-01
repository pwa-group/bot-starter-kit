<?php

namespace App\Controllers;

use App\Dictionary;
use App\Viewer;
use TelegramBot\Api\Client;

class Index
{
    public function __invoke(int $id, Client $bot, $sendOnly = false): void
    {
        Viewer::view(
            $id,
            $bot,
            Dictionary::config()->get('banner'),
            "🖐 Добрый день!
Для начала работы с ботом Вам необходимо передать менеджеру свой 🆔ID.
‼️Он нужен для определения ваших 📱PWA прил.
Вот ваш ID передайте его 👨‍💻менеджеру:
<b>$id</b>",
            null,
            null,
            $sendOnly);
    }
}
