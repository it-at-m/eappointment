<?php

namespace BO\Slim\Middleware\Session;

class SessionContainer implements SessionInterface
{
    private $sessionContainer;

    private $sessionLoader;

    public static function fromContainer(callable $sessionLoader)
    {
        $instance = new static();
        $instance->sessionLoader = $sessionLoader;
        return $instance;
    }

    public function setGroup(array $group, $clear = false)
    {
        $this->getSession()->setGroup($group, $clear);
    }

    public function writeData()
    {
        $this->getSession()->writeData();
    }

    public function set($key, $value, $index = null)
    {
        $this->getSession()->set($key, $value, $index);
    }

    public function get($key, $index = null, $default = null)
    {
        return $this->getSession()->get($key, $index, $default);
    }

    public function getEntity()
    {
        return $this->getSession()->getEntity();
    }

    public function remove($key, $groupIndex = null)
    {
        $this->getSession()->remove($key, $groupIndex);
    }

    public function clear()
    {
        $this->getSession()->clear();
    }

    public function restart()
    {
        $this->getSession()->restart();
    }

    public function clearGroup($groupIndex = null)
    {
        $this->getSession()->clearGroup($groupIndex);
    }

    public function has($key, $groupIndex = null)
    {
        return $this->getSession()->has($key, $groupIndex);
    }

    public function isEmpty()
    {
        return $this->getSession()->isEmpty();
    }

    public function jsonSerialize()
    {
        return $this->getSession()->jsonSerialize();
    }

    private function getSession()
    {
        if (!$this->sessionContainer) {
            $this->sessionContainer = $this->loadSession();
        }
        return $this->sessionContainer;
    }

    private function loadSession()
    {
        $sessionLoader = $this->sessionLoader;
        return $sessionLoader();
    }
}
