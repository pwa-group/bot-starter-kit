<?php

namespace App\Controllers;

use App\API;
use App\Dictionary;
use PWAGroup\Models\FBP;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Message;

class FBPixel
{
    public function pwas(int $id, Client $bot): void
    {
        $pwas = API::PWAGroup()->getPWAs($id);
        $buttons = null;
        foreach ($pwas as $pwa) {
            $buttons[] = [
                ['text' => "🛠 {$pwa->getAlias()}", 'callback_data' => "pwas/{$pwa->getID()}/fbps"],
            ];
        }
        $keyboard = $buttons === null ? null : new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($buttons);
        /** @var Message $message */
        $message = $bot->sendPhoto(
            $id,
            new \CURLFile(Dictionary::config()->get('pwab')),
            "Список ваших 📱PWA.\nДля редактирования 🛠 Facebook Pixel'лей нажмите на названия 📱PWA",
            null,
            $keyboard,
            false,
            'html',
        );
        $_SERVER['messageId'] = $message->getMessageId();
    }

    public function index(int $id, Client $bot, string $pwaId): void
    {
        $pwa = API::PWAGroup()->getPWA($pwaId);
        $buttons[] = [
            ['text' => 'Назад', 'callback_data' => "pwas/fbps"],
            ['text' => 'Добавить', 'callback_data' => "pwas/{$pwa->getID()}/fbps/add"],
        ];
        foreach ($pwa->getFBPs() as $FBP) {
            $buttons[] = [
                ['text' => '🔗' . substr($FBP->getID(), 0, 4) . '...' . substr($FBP->getID(), strlen($FBP->getID()) - 4, 4) . ':' . ($FBP->getLead() === 'install' ? 'уст' : 'рег'), 'url' => "https://{$pwa->getDomain()}/?fbp={$FBP->getID()}"],
                ['text' => "На " . ($FBP->getLead() === 'install' ? 'регистрацию' : 'установку'), 'callback_data' => "pwas/{$pwa->getID()}/fbps/{$FBP->getID()}/" . ($FBP->getLead() === 'install' ? 'registration' : 'install')],
                ['text' => 'Удалить', 'callback_data' => "pwas/{$pwa->getID()}/fbps/{$FBP->getID()}/delete"]
            ];
        }
        $keyboard = $buttons === null ? null : new \TelegramBot\Api\Types\Inline\InlineKeyboardMarkup($buttons);
        /** @var Message $message */
        $message = $bot->sendPhoto(
            $id,
            new \CURLFile(Dictionary::config()->get('fbpb')),
            "📱PWA {$pwa->getAlias()}.\nСписок ваших 🛠 Facebook Pixel'лей.\nДля добавления пикселя воспользуйтесь кнопкой добавить.\nЧто бы изменить события которое считать лидом нажмите на кнопку лид или рега\nДля удаления пикселя нажмите на кнопку удалить",
            null,
            $keyboard,
            false,
            'html',
        );
        $_SERVER['messageId'] = $message->getMessageId();
    }

    public function create(int $id, Client $bot, string $pwaId): void
    {
        $_SESSION['pwaId'] = $pwaId;
        /** @var Message $message */
        $message = $bot->sendPhoto(
            $id,
            new \CURLFile(Dictionary::config()->get('fbpb')),
            "Добавте пиксеи построчно в формате <b>pixel:lead</b>, где <b>pixel</b> - это ваши FB pixel'ли, а <b>lead</b> - события лида которое может принимать занчения <b>install</b> или <b>registration</b>",
            null,
            null,
            false,
            'html',
        );
        $_SERVER['messageId'] = $message->getMessageId();
    }

    public function save(string $text, string $pwaId)
    {
        $pwa = API::PWAGroup()->getPWA($pwaId);
        $FBPs = $pwa->getFBPs();
        $rows = explode("\n", $text);
        foreach ($rows as $row) {
            $row = explode(':', $row);
            try {
                $FBPs[] = new FBP([
                    'id' => trim($row[0]),
                    'lead' => trim($row[1])
                ]);
            } catch (\Error $e) {
            }
        }
        $pwa->setFBPs($FBPs);
        API::PWAGroup()->savePWA($pwa);
    }

    public function delete(int $id, Client $bot, string $pwaId, string $fbp): void
    {
        $pwa = API::PWAGroup()->getPWA($pwaId);
        $FBPs = $pwa->getFBPs();
        foreach ($FBPs as $key => $FBP) {
            if ($FBP->getID() == $fbp) {
                unset($FBPs[$key]);
            }
        }
        $pwa->setFBPs($FBPs);
        API::PWAGroup()->savePWA($pwa);
        $this->index($id, $bot, $pwaId);
    }

    public function install(int $id, Client $bot, string $pwaId, string $fbp): void
    {
        $this->switcher($id, $bot, $pwaId, $fbp, 'install');
    }

    public function registration(int $id, Client $bot, string $pwaId, string $fbp): void
    {
        $this->switcher($id, $bot, $pwaId, $fbp, 'registration');
    }

    private function switcher(int $id, Client $bot, string $pwaId, string $fbp, string $lead): void
    {
        $pwa = API::PWAGroup()->getPWA($pwaId);
        $FBPs = $pwa->getFBPs();
        foreach ($FBPs as $key => $FBP) {
            if ($FBP->getID() == $fbp) {
                $FBPs[$key] = new FBP(['id' => $fbp, 'lead' => $lead]);
            }
        }
        $pwa->setFBPs($FBPs);
        API::PWAGroup()->savePWA($pwa);
        $this->index($id, $bot, $pwaId);
    }
}
