<?php

namespace App\Controllers;

use App\Dictionary;
use App\Viewer;
use TelegramBot\Api\Client;

class Profile
{
    public function __invoke(int $id, Client $bot): void
    {
        Viewer::view(
            $id,
            $bot,
            Dictionary::config()->get('banner'),
            "Вот ваш 🆔ID передайте его 👨‍💻менеджеру:
<b>$id</b>");
    }
}
