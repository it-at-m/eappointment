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

    #[\Override]
    public function setGroup(array $group, $clear = false)
    {
        $this->getSession()->setGroup($group, $clear);
    }

    public function writeData()
    {
        $this->getSession()->writeData();
    }

    #[\Override]
    public function set($key, $value, $groupIndex = null)
    {
        $this->getSession()->set($key, $value, $groupIndex);
    }

    #[\Override]
    public function get($key, $groupIndex = null, $default = null)
    {
        return $this->getSession()->get($key, $groupIndex, $default);
    }

    #[\Override]
    public function getEntity()
    {
        return $this->getSession()->getEntity();
    }

    #[\Override]
    public function remove($key, $groupIndex = null)
    {
        $this->getSession()->remove($key, $groupIndex);
    }

    #[\Override]
    public function clear()
    {
        $this->getSession()->clear();
    }

    public function restart()
    {
        $this->getSession()->restart();
    }

    #[\Override]
    public function clearGroup($groupIndex = null)
    {
        $this->getSession()->clearGroup($groupIndex);
    }

    #[\Override]
    public function has($key, $groupIndex = null)
    {
        return $this->getSession()->has($key, $groupIndex);
    }

    #[\Override]
    public function isEmpty()
    {
        return $this->getSession()->isEmpty();
    }

    // TODO: Review the use of `mixed` return type.
    // This method delegates to getSession()->jsonSerialize(), which may return various types.
    // Consider adding stricter return typing if getSession() can be more precisely typed.
    #[\Override]
    public function jsonSerialize(): mixed
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
