<?php

namespace App;

class Dictionary
{
    const CACHE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache';
    const CONFIG_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'config.php';
    const TEMPLATE_CONFIG_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'config.php.tmp';
    const AUTH_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'cache' . DIRECTORY_SEPARATOR . 'auth.txt';

    private static $instances = [];

    /**
     * The Singleton's constructor should always be private to prevent direct
     * construction calls with the `new` operator.
     */
    protected function __construct()
    {
    }

    /**
     * Singletons should not be cloneable.
     */
    protected function __clone()
    {
    }

    /**
     * Singletons should not be restorable from strings.
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }

    public static function config(): Dictionary
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function init()
    {
        Dictionary::config()->set(require_once Dictionary::CONFIG_PATH);
    }

    public function set(array|string $config, string|null $value = null): void
    {
        if ($value == null) {
            $this->config = $config;
        } else {
            $this->config[$config] = $value;
        }
    }

    public function get($config = null): array|string
    {
        return $this->config[$config] ?? $this->config;
    }

    public function save(): void
    {
        file_put_contents(\App\Dictionary::CONFIG_PATH, '<?php return ' . var_export($this->config, true) . ';');
    }

    private array $config;
}
