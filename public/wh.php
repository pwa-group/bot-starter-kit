<?php

use App\API;
use App\Controllers\FBPixel;
use App\Controllers\Index;
use App\Controllers\Profile;
use App\Controllers\PWAs;
use App\Dictionary;
use App\Logger;
use App\Route;
use App\Router;
use App\StringHelpers;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\CallbackQuery;
use TelegramBot\Api\Types\Update;

require_once('..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
Dictionary::config()->init();
API::PWAGroup()->setKey(Dictionary::config()->get('api'));
$router = new Router([
    new Route('📱 PWA', [PWAs::class, 'index']),
    new Route('pwas/fbps', [FBPixel::class, 'pwas']),
    new Route('pwas/{pwaId}', [PWAs::class, 'view']),
    new Route('pwas/{pwaId}/fbps', [FBPixel::class, 'index']),
    new Route('pwas/{pwaId}/fbps/add', [FBPixel::class, 'create']),
    new Route('pwas/{pwaId}/fbps/{page}', [FBPixel::class, 'index']),
    new Route('pwas/{pwaId}/fbps/{fbp}/delete', [FBPixel::class, 'delete']),
    new Route('pwas/{pwaId}/fbps/{fbp}/install', [FBPixel::class, 'install']),
    new Route('pwas/{pwaId}/fbps/{fbp}/registration', [FBPixel::class, 'registration']),
]);
function handler(int $id, Client $bot, Router $router, string $data = '/')
{
    $route = $router->matchFromPath($data);
    $parameters = $route->getParameters();
    $arguments['id'] = $id;
    $arguments['bot'] = $bot;
    $arguments = array_merge($arguments, $route->getVars());
    $controllerName = $parameters[0];
    $methodName = $parameters[1] ?? null;
    $controller = new $controllerName();
    if (!is_callable($controller)) {
        $controller = [$controller, $methodName];
    }
    try {
        $controller(...array_values($arguments));
    } catch (Error $e) {
        Logger::telegram(var_export($e, true));
    }
}

try {
    $bot = new Client(Dictionary::config()->get('bot'));
    $bot->command('start', function ($message) use ($bot, $router) {
        $id = $message->getChat()->getId();
        session_id($id);
        session_start();
        (new Index)($id, $bot, true);
    });
    $bot->callbackQuery(function (CallbackQuery $callbackQuery) use ($bot, $router) {
        $id = $callbackQuery->getFrom()->getId();
        session_id($id);
        session_start();
        $data = $callbackQuery->getData();
        handler($id, $bot, $router, $data);
    });
    //Handle text messages
    $bot->on(function (Update $update) use ($bot, $router) {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        $bot->deleteMessage($id, $message->getMessageId());
        session_id($id);
        session_start();
        if (isset($_SESSION['pwaId'])) {
            $pwaId = $_SESSION['pwaId'];
            unset($_SESSION['pwaId']);
            $FBPixel = new FBPixel;
            $FBPixel->save($message->getText(), $pwaId);
            $FBPixel->index($id, $bot, $pwaId);
        } else {
            $text = StringHelpers::removeEmoji($message->getText());
            $text = trim($text);
            switch ($text) {
                case 'Мой 🆔':
                    (new Profile)($id, $bot);
                    break;
                case 'PWA прилы':
                    (new PWAs)->index($id, $bot);
                    break;
                case 'Facebook Pixel':
                    (new FBPixel)->pwas($id, $bot);
                    break;
                default:
                    (new Index)($id, $bot);
                    break;
            }
        }
    }, function () {
        return true;
    });
    $bot->run();
} catch (\TelegramBot\Api\Exception $e) {
}
