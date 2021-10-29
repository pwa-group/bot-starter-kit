<?php

namespace App;

class Logger
{
    public static function telegram($text)
    {
        file_put_contents(Dictionary::CACHE_PATH . DIRECTORY_SEPARATOR . 'telegram.log', $text . PHP_EOL, FILE_APPEND);
    }
}
