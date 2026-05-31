<?php

namespace BO\Slim\Middleware\Session;

class SessionContainer implements SessionInterface
{
    private $sessionContainer;

    private $sessionLoader;

    public static function fromContainer(callable $sessionLoader): static
    {
        $instance = new static();
        $instance->sessionLoader = $sessionLoader;
        return $instance;
    }

    /**
     * @return void
     */
    public function setGroup(array $group, $clear = false)
    {
        $this->getSession()->setGroup($group, $clear);
    }

    public function writeData(): void
    {
        $this->getSession()->writeData();
    }

    /**
     * @param null|string $index
     *
     * @psalm-param array|int<0, max> $value
     * @psalm-param 'human'|null $index
     *
     * @return void
     */
    public function set(string $key, array|int $value, string|null $index = null)
    {
        $this->getSession()->set($key, $value, $index);
    }

    /**
     * @param null|string $index
     *
     * @psalm-param 'human'|null $index
     */
    public function get(string $key, string|null $index = null, $default = null)
    {
        return $this->getSession()->get($key, $index, $default);
    }

    public function getEntity()
    {
        return $this->getSession()->getEntity();
    }

    /**
     * @return void
     */
    public function remove($key, $groupIndex = null)
    {
        $this->getSession()->remove($key, $groupIndex);
    }

    /**
     * @return void
     */
    public function clear()
    {
        $this->getSession()->clear();
    }

    public function restart(): void
    {
        $this->getSession()->restart();
    }

    /**
     * @return void
     */
    public function clearGroup($groupIndex = null)
    {
        $this->getSession()->clearGroup($groupIndex);
    }

    /**
     * @param null|string $groupIndex
     *
     * @psalm-param 'human'|null $groupIndex
     */
    public function has(string $key, string|null $groupIndex = null)
    {
        return $this->getSession()->has($key, $groupIndex);
    }

    public function isEmpty()
    {
        return $this->getSession()->isEmpty();
    }

    // TODO: Review the use of `mixed` return type.
    // This method delegates to getSession()->jsonSerialize(), which may return various types.
    // Consider adding stricter return typing if getSession() can be more precisely typed.
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
