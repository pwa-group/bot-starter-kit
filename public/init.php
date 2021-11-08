<?php

use App\Dictionary;
use TelegramBot\Api\BotApi;

require_once('..' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php');
$webhookURL = 'https://' . $_SERVER['HTTP_HOST'] . '/wh.php';
if (!file_exists(Dictionary::CACHE_PATH)) {
    mkdir(Dictionary::CACHE_PATH);
}
Dictionary::config()->set(['api' => '', 'bot' => '', 'banner' => '', 'fbpb' => '', 'pwab' => '']);
$files = ['banner' => 'Основной баннер', 'fbpb' => 'Баннер для Facebook Pixel', 'pwab' => 'Баннер для PWA'];
if (file_exists(Dictionary::CONFIG_PATH)) {
    Dictionary::config()->init();
    if (!empty($_POST)) {
        Dictionary::config()->set('api', $_POST['api']);
        Dictionary::config()->set('bot', $_POST['bot']);
        foreach ($files as $file => $label) {
            if (isset($_FILES[$file]['tmp_name']) && $_FILES[$file]['tmp_name'] != '') {
                $target_file = __DIR__ . DIRECTORY_SEPARATOR . basename($_FILES[$file]['name']);
                if (move_uploaded_file($_FILES[$file]['tmp_name'], $target_file)) {
                    if (file_exists(Dictionary::config()->get($file))) {
                        unlink(Dictionary::config()->get($file));
                    }
                    Dictionary::config()->set($file, $target_file);
                }
            }
        }
        Dictionary::config()->save();
        $bot = new BotApi(Dictionary::config()->get('bot'));
        try {
            $bot->setWebhook($webhookURL);
        } catch (\TelegramBot\Api\Exception $e) {
        }
    }
} else {
    copy(Dictionary::TEMPLATE_CONFIG_PATH, Dictionary::CONFIG_PATH);
}
$bot = new BotApi(
    Dictionary::config()->get('bot')
);
try {
    $botWebhookURL = $bot->getWebhookInfo()->getUrl();
} catch (\TelegramBot\Api\Exception $e) {
    $botWebhookURL = '';
}
$requirements = [
    [
        'name' => 'Поддержка PHP 8',
        'description' => 'Необходима версия 8 или выше',
        'condition' => version_compare(phpversion(), '8.0', '>=')
    ],
    [
        'name' => 'Доступ на запись',
        'description' => 'Необходимо для кеширования',
        'condition' => is_writable(Dictionary::CACHE_PATH)
    ],
    [
        'name' => 'Файл конфига',
        'description' => 'Для хранения настроек',
        'condition' => is_writable(Dictionary::CONFIG_PATH)
    ],
    [
        'name' => 'PWA GROUP API',
        'description' => 'Для доступа к PWA GROUP',
        'condition' => strlen(Dictionary::config()->get('api')) > 0
    ],
    [
        'name' => 'Telegram Bot API',
        'description' => 'Для доступа к боту',
        'condition' => strlen(Dictionary::config()->get('bot')) > 0
    ],
    [
        'name' => 'Webhook Telegram',
        'description' => 'Настройка бота',
        'condition' => $botWebhookURL === $webhookURL
    ],
    [
        'name' => 'Поддержка PHP cURL',
        'description' => 'Для работы с файлами',
        'condition' => class_exists('CURLFile')
    ],
    [
        'name' => '3 банера',
        'description' => 'Красивый бот будет',
        'condition' => Dictionary::config()->get('banner') && Dictionary::config()->get('fbpb') && Dictionary::config()->get('pwab')
    ]
];
$conformity = 0;
foreach ($requirements as $requirement) {
    if ($requirement['condition']) {
        $conformity++;
    }
}
$conformityClass = $conformity === count($requirements) ? 'success' : 'warning'
?>
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <title>Установка telegram bot</title>
    <meta name="theme-color" content="#7952b3">
    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

        .container {
            max-width: 960px;
        }
    </style>
</head>
<body class="bg-dark">
<div class="container">
    <main>
        <div class="py-5 text-center text-white">
            <img class="d-block mx-auto mb-4" src="https://dash.pwa.group/static/images/logo_new.svg" alt="" width="246"
                 height="46">
            <h2>Установка telegram bot</h2>
            <p class="lead">С помощью этого скрипта вы быстро и удобно сможете настроить свой хостинг. Для начала вам
                необходимо получить API ключ на платформе <a class="link-success" href="https://pwa.group">PWA GROUP</a>.
                После этого создать бота в telegram, для примера
                воспользуйтесь этой <a class="link-success"
                                       href="https://github.com/pwa-group/bot-starter-kit/blob/master/README.md#Создание-telegram-бота">инструкцией</a>.
            </p>
        </div>
        <div class="row g-5">
            <div class="col-md-5 col-lg-4 order-md-last">
                <h4 class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-<?= $conformityClass ?>">Соответствия</span>
                    <span class="badge bg-<?= $conformityClass ?> rounded-pill"><?= $conformity ?></span>
                </h4>
                <ul class="list-group mb-3">
                    <?php foreach ($requirements as $requirement) : ?>
                        <li class="list-group-item d-flex justify-content-between lh-sm">
                            <div>
                                <h6 class="my-0"><?= $requirement['name'] ?></h6>
                                <small class="text-muted"><?= $requirement['description'] ?></small>
                            </div>
                            <?php if ($requirement['condition']) : ?>
                                <span class="text-success">Есть</span>
                            <?php else : ?>
                                <span class="text-danger">Нету</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="col-md-7 col-lg-8">
                <h4 class="mb-3 text-white">Форма конфига</h4>
                <form class="needs-validation" novalidate method="post" action="/init.php"
                      enctype="multipart/form-data">
                    <div class="row g-3">
                        <div class="col-12">
                            <label for="api" class="form-label text-white">API ключ PWA GROUP</label>
                            <div class="input-group has-validation">
                                <input type="text" class="form-control" id="api" placeholder="hx2xzLYxxfKnxqxE" required
                                       name="api"
                                       value="<?= Dictionary::config()->get('api') ?>">
                                <div class="invalid-feedback">
                                    Заполните API ключ PWA GROUP
                                </div>
                            </div>
                            <small class="text-muted">Выдаётся админом PWA GROUP</small>
                        </div>
                        <div class="col-12">
                            <label for="bot" class="form-label text-white">Telegram Bot API</label>
                            <div class="input-group has-validation">
                                <input type="text" class="form-control" id="bot"
                                       placeholder="1426535579:DUx569QpX2AUItjdAFK5T2YwSs-LFdjEXAw" required
                                       name="bot"
                                       value="<?= Dictionary::config()->get('bot') ?>">
                                <div class="invalid-feedback">
                                    Заполните Telegram Bot API
                                </div>
                            </div>
                            <small class="text-muted">Необходимо создать бота в telegram</small>
                        </div>
                    </div>
                    <div class="row g-3">
                        <?php foreach ($files as $file => $label) : ?>
                            <?php if (Dictionary::config()->get($file)) {
                                $type = pathinfo(Dictionary::config()->get($file), PATHINFO_EXTENSION);
                                $data = file_get_contents(Dictionary::config()->get($file));
                                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                echo "<div class='col-6'><img src='{$base64}' class='img-fluid rounded mx-auto d-block'></div>";
                            } ?>
                            <div class="<?= (Dictionary::config()->get($file) ? 'col-6' : 'col-12') ?>">
                                <label for="<?= $file ?>" class="form-label text-white"><?= $label ?></label>
                                <input class="form-control" type="file" id="<?= $file ?>" name="<?= $file ?>">
                                <small class="text-muted">Если оставить пустым старый баннер не удалится</small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr class="my-4">
                    <button class="w-100 btn btn-success btn-lg" type="submit">Сохранить</button>
                </form>
            </div>
        </div>
        <div class="py-5 text-center text-white">
            <p class="lead text-danger">После ввода всех необходимых данных, удалите этот скрипт со своего хостинга!</p>
        </div>
    </main>

    <footer class="my-5 pt-5 text-muted text-center text-small">
        <p class="mb-1">&copy; 2021 PWA GROUP</p>
        <ul class="list-inline">
            <li class="list-inline-item"><a class="link-success" target="_blank" href="https://t.me/pwagroupadmin">Служба
                    поддержки</a></li>
            <li class="list-inline-item"><a class="link-success" target="_blank" href="https://pwa.group/faq/">FAQ</a>
            </li>
        </ul>
    </footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p"
        crossorigin="anonymous"></script>
<script>
  (function () {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
      .forEach(function (form) {
        form.addEventListener('submit', function (event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
  })()
</script>
</body>
</html>
