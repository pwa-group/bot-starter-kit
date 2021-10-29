<?php

namespace App\Controllers;

use App\API;
use App\Dictionary;
use App\Templates\Keyboard;
use TelegramBot\Api\Client;

class PWAs
{
    public function index(int $id, Client $bot): void
    {
        $pwas = API::PWAGroup()->getPWAs($id);
        $buttons = null;
        foreach ($pwas as $pwa) {
            $locales = empty($pwa->getLocales()) ? '' : '[' . implode(', ', $pwa->getLocales()) . ']';
            $buttons[] = [
                ['text' => '👀 ' . $locales . $pwa->getAlias(), 'url' => 'https://' . $pwa->getDomain() . '/'],
                ['text' => 'Получить 🔗ссылку', 'callback_data' => "pwas/{$pwa->getID()}"],
            ];
        }
        $keyboard = $buttons === null ? null : new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($buttons);
        $bot->sendPhoto(
            $id,
            new \CURLFile(Dictionary::config()->get('pwab')),
            "Список ваших 📱PWA.\nДля получения 🔗ссылки нажмите на кнопку <b>\"Получить 🔗ссылку\"</b> в списка 📱PWA.\nДля 👀 предпросмотра нажмите на названия 📱PWA.",
            null,
            $keyboard,
            false,
            'html',
        );
    }

    public function view(int $id, Client $bot, string $pwaId): void
    {
        $pwa = API::PWAGroup()->getPWA($pwaId);
        $bot->sendMessage($id, 'https://' . $pwa->getDomain() . '/', null, false, null, new Keyboard());
    }
}
