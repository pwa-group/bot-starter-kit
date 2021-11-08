<?php

namespace App;

use PWAGroup\Auth;
use PWAGroup\Models\PWA;
use PWAGroup\PWAs\Pages;

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
        if (file_exists(Dictionary::AUTH_PATH) && filemtime(Dictionary::AUTH_PATH) >= (time() - 60 * 60)) {
            $this->auth = unserialize(file_get_contents(Dictionary::AUTH_PATH));
        } else {
            $this->auth = new Auth($key);
            file_put_contents(Dictionary::AUTH_PATH, serialize($this->auth), LOCK_EX);
        }
    }

    private Auth $auth;

    /**
     * @return PWA[]
     */
    public function getPWAs($id): array
    {
        $pages = new Pages($this->auth, 5);
        $pages->setFilter('status', PWA::STATUS_RUN);
        $pages->setFilter('tags', $id);
        return $pages->getPage();
    }

    public function savePWA(PWA $pwa)
    {
        $PWA = new \PWAGroup\PWAs\PWA($this->auth);
        $PWA->update($pwa);
    }

    /**
     * @return PWA
     */
    public function getPWA($id): PWA
    {
        $PWA = new \PWAGroup\PWAs\PWA($this->auth);
        return $PWA->read($id);
    }
}
