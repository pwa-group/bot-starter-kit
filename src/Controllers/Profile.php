<?php

namespace App\Controllers;

use App\Templates\Keyboard;
use TelegramBot\Api\Client;

class Profile
{
    public function __invoke(int $id, Client $bot): void
    {
        $bot->sendMessage($id, "Вот ваш 🆔ID передайте его 👨‍💻менеджеру:
<b>$id</b>", 'html', false, null, new Keyboard());
    }
}
