<?php

namespace App;

use App\Templates\Keyboard;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\InputMedia\InputMediaPhoto;
use TelegramBot\Api\Types\Message;

class Viewer
{
    public static function view(int $id, Client $bot, string $banner, string $caption, $inlineKeyboardMarkup = null, $replyKeyboardMarkup = null, $sendOnly = false)
    {
        if (isset($_SESSION['messageId']) && !$sendOnly) {
            self::update($id, $bot, $banner, $caption, $inlineKeyboardMarkup, $replyKeyboardMarkup);
        } else {
            self::send($id, $bot, $banner, $caption, $inlineKeyboardMarkup, $replyKeyboardMarkup);
        }
    }

    private static function send(int $id, Client $bot, string $banner, string $caption, $inlineKeyboardMarkup = null, $replyKeyboardMarkup = null)
    {
        /** @var Message $message */
        $message = $bot->sendPhoto(
            $id,
            new \CURLFile($banner),
            $caption,
            null,
            $inlineKeyboardMarkup ?? $replyKeyboardMarkup ?? new Keyboard(),
            false,
            'html',
        );
        Logger::telegram('Send ' . $message->getMessageId());
        $_SESSION['messageId'] = $message->getMessageId();
    }

    private static function update(int $id, Client $bot, string $banner, string $caption, $inlineKeyboardMarkup = null, $replyKeyboardMarkup = null)
    {
        Logger::telegram('Update ' . $_SESSION['messageId']);
        try {
            /** @var bool|Message $message */
            $message = $bot->editMessageMedia(
                $id,
                $_SESSION['messageId'],
                new InputMediaPhoto("https://{$_SERVER['HTTP_HOST']}/" . basename($banner), $caption, 'html'),
                null,
                $inlineKeyboardMarkup,
            );
            $_SESSION['messageId'] = $message->getMessageId();
        } catch (\Exception $e) {
            self::send($id, $bot, $banner, $caption, $inlineKeyboardMarkup, $replyKeyboardMarkup);
        }
    }
}
