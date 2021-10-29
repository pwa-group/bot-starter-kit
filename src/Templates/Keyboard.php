<?php

namespace App\Templates;

use TelegramBot\Api\Types\ReplyKeyboardMarkup;

class Keyboard extends ReplyKeyboardMarkup
{
    public function __construct()
    {
        parent::__construct([
            ['📱 PWA прилы', '🛠 Facebook Pixel'],
            ['Мой 🆔']
        ], true, true, true);
    }
}
