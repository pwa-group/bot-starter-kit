<?php

namespace App;

class API
{
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

    public static function PWAGroup(): API
    {
        $cls = static::class;
        if (!isset(self::$instances[$cls])) {
            self::$instances[$cls] = new static();
        }

        return self::$instances[$cls];
    }

    public function setKey(string $key)
    {
        if (file_exists(\App\Dictionary::AUTH_PATH) && filemtime(\App\Dictionary::AUTH_PATH) >= (time() - 60 * 60)) {
            $this->auth = unserialize(file_get_contents(\App\Dictionary::AUTH_PATH));
        } else {
            $this->auth = new \PWAGroup\Auth($key);
            file_put_contents(\App\Dictionary::AUTH_PATH, serialize($this->auth), LOCK_EX);
        }
    }

    private \PWAGroup\Auth $auth;

    /**
     * @return \PWAGroup\Models\PWA[]
     */
    public function getPWAs($id): array
    {
        $pages = new \PWAGroup\PWAs\Pages($this->auth, 5);
        $pages->setFilter('status', \PWAGroup\Models\PWA::STATUS_RUN);
        $pages->setFilter('tags', $id);
        return $pages->getPage();
    }

    public function savePWA(\PWAGroup\Models\PWA $pwa)
    {
        $PWA = new \PWAGroup\PWAs\PWA($this->auth);
        $PWA->update($pwa);
    }

    /**
     * @return \PWAGroup\Models\PWA
     */
    public function getPWA($id): \PWAGroup\Models\PWA
    {
        $PWA = new \PWAGroup\PWAs\PWA($this->auth);
        return $PWA->read($id);
    }
}
