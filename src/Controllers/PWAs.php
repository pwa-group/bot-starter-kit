<?php

namespace App\Controllers;

use App\API;
use App\Dictionary;
use App\Viewer;
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
                ['text' => '🔗' . $pwa->getDomain(), 'callback_data' => "pwas/{$pwa->getID()}"],
            ];
        }
        $inlineKeyboardMarkup = $buttons === null ? null : new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($buttons);
        $caption = "Список ваших 📱PWA.\nДля получения 🔗ссылки нажмите на названия домена в списка 📱PWA.\nДля 👀 предпросмотра нажмите на названия 📱PWA.";
        Viewer::view($id, $bot, Dictionary::config()->get('pwab'), $caption, $inlineKeyboardMarkup);
    }

    public function view(int $id, Client $bot, string $pwaId): void
    {
        $pwa = API::PWAGroup()->getPWA($pwaId);
        Viewer::view($id, $bot, Dictionary::config()->get('pwab'), 'https://' . $pwa->getDomain() . '/');
    }
}
