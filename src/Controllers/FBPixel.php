<?php

namespace App\Controllers;

use App\API;
use App\Dictionary;
use App\Pagination;
use App\Viewer;
use PWAGroup\Models\FBP;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class FBPixel
{
    public function pwas(int $id, Client $bot): void
    {
        $pwas = API::PWAGroup()->getPWAs($id);
        $buttons = null;
        foreach ($pwas as $pwa) {
            $locales = empty($pwa->getLocales()) ? '' : '[' . implode(', ', $pwa->getLocales()) . ']';
            $buttons[] = [
                ['text' => "🛠 {$locales} {$pwa->getAlias()} {$pwa->getDomain()}", 'callback_data' => "pwas/{$pwa->getID()}/fbps"],
            ];
        }
        $caption = "Список ваших 📱PWA.\nДля редактирования 🛠 Facebook Pixel'лей нажмите на названия 📱PWA";
        $inlineKeyboardMarkup = $buttons === null ? null : new InlineKeyboardMarkup($buttons);
        Viewer::view(
            $id,
            $bot,
            Dictionary::config()->get('pwab'),
            $caption,
            $inlineKeyboardMarkup);
    }

    public function index(int $id, Client $bot, string $pwaId, int $currentPage = 1): void
    {
        $pwa = API::PWAGroup()->getPWA($pwaId);
        $buttons[] = [
            ['text' => '🔙Назад', 'callback_data' => "pwas/fbps"],
            ['text' => '➕Добавить', 'callback_data' => "pwas/{$pwa->getID()}/fbps/add"],
        ];
        $pagination = new Pagination($currentPage, $pwa->getFBPs());
        foreach ($pagination->getModels() as $FBP) {
            $buttons[] = [
                ['text' => '🔗' . substr($FBP->getID(), 0, 4) . '...' . substr($FBP->getID(), strlen($FBP->getID()) - 4, 4) . ':' . ($FBP->getLead() === 'install' ? 'уст' : 'рег'), 'url' => "https://{$pwa->getDomain()}/?fbp={$FBP->getID()}"],
                ['text' => "На " . ($FBP->getLead() === 'install' ? '📝регистра' : '⤵установку'), 'callback_data' => "pwas/{$pwa->getID()}/fbps/{$FBP->getID()}/" . ($FBP->getLead() === 'install' ? 'registration' : 'install')],
                ['text' => '🗑Удалить', 'callback_data' => "pwas/{$pwa->getID()}/fbps/{$FBP->getID()}/delete"]
            ];
        }
        $paginationButtons = $pagination->getButtons("pwas/{$pwa->getID()}/fbps");
        if ($paginationButtons) {
            $buttons[] = $paginationButtons;
        }
        $inlineKeyboardMarkup = $buttons === null ? null : new InlineKeyboardMarkup($buttons);
        $caption = "📱PWA {$pwa->getAlias()}.\nСписок ваших 🛠 Facebook Pixel'лей.\nДля добавления пикселя воспользуйтесь кнопкой <b>➕Добавить</b>.\nЧто бы изменить события которое считать лидом нажмите на кнопку лид или рега\nДля удаления пикселя нажмите на кнопку 🗑удалить" . $pagination->getCaption();
        Viewer::view(
            $id,
            $bot,
            Dictionary::config()->get('fbpb'),
            $caption,
            $inlineKeyboardMarkup);
    }

    public function create(int $id, Client $bot, string $pwaId): void
    {
        $_SESSION['pwaId'] = $pwaId;
        $caption = "Добавте пиксеи построчно в формате <b>pixel:lead</b>, где <b>pixel</b> - это ваши FB pixel'ли, а <b>lead</b> - события лида которое может принимать занчения <b>install</b> или <b>registration</b>";
        Viewer::view(
            $id,
            $bot,
            Dictionary::config()->get('fbpb'),
            $caption);
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
