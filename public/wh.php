<?php
require_once('..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
$router = new \App\Router([
    new \App\Route('📱 PWA', [\App\Controllers\PWAs::class, 'index']),
    new \App\Route('pwas/fbps', [\App\Controllers\FBPixel::class, 'pwas']),
    new \App\Route('pwas/{pwaId}', [\App\Controllers\PWAs::class, 'view']),
    new \App\Route('pwas/{pwaId}/fbps', [\App\Controllers\FBPixel::class, 'index']),
    new \App\Route('pwas/{pwaId}/fbps/add', [\App\Controllers\FBPixel::class, 'create']),
    new \App\Route('pwas/{pwaId}/fbps/{fbp}/delete', [\App\Controllers\FBPixel::class, 'delete']),
    new \App\Route('pwas/{pwaId}/fbps/{fbp}/install', [\App\Controllers\FBPixel::class, 'install']),
    new \App\Route('pwas/{pwaId}/fbps/{fbp}/registration', [\App\Controllers\FBPixel::class, 'registration']),
]);
function handler(int $id, \TelegramBot\Api\Client $bot, \App\Router $router, string $data = '/')
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
    } catch (\Error $e) {
        \App\Logger::telegram(var_export($e, true));
    }
}

$config = require_once \App\Dictionary::CONFIG_PATH;
\App\API::PWAGroup()->setKey($config['api']);
try {
    $bot = new \TelegramBot\Api\Client($config['bot']);
    //Handle /ping command
    $bot->command('start', function ($message) use ($bot, $router) {
        $id = $message->getChat()->getId();
        handler($id, $bot, $router);
    });
    $bot->callbackQuery(function (\TelegramBot\Api\Types\CallbackQuery $callbackQuery) use ($bot, $router) {
        $id = $callbackQuery->getFrom()->getId();
        $data = $callbackQuery->getData();
        handler($id, $bot, $router, $data);
    });
    //Handle text messages
    $bot->on(function (\TelegramBot\Api\Types\Update $update) use ($bot, $router) {
        $message = $update->getMessage();
        $id = $message->getChat()->getId();
        session_id($id);
        session_start();
        if (isset($_SESSION['pwaId'])) {
            $pwaId = $_SESSION['pwaId'];
            session_destroy();
            $FBPixel = new \App\Controllers\FBPixel;
            $FBPixel->save($message->getText(), $pwaId);
            $FBPixel->index($id, $bot, $pwaId);
        } else {
            $text = \App\StringHelpers::removeEmoji($message->getText());
            $text = trim($text);
            switch ($text) {
                case 'Мой 🆔':
                    (new \App\Controllers\Profile)($id, $bot);
                    break;
                case 'PWA прилы':
                    (new \App\Controllers\PWAs)->index($id, $bot);
                    break;
                case 'Facebook Pixel':
                    (new \App\Controllers\FBPixel)->pwas($id, $bot);
                    break;
                default:
                    (new \App\Controllers\Index)($id, $bot);
                    break;
            }
        }
    }, function () {
        return true;
    });
    $bot->run();
} catch (\TelegramBot\Api\Exception $e) {
}
