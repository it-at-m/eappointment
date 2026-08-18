<?php

namespace BO\Slim\Middleware\Session;

class SessionContainer implements SessionInterface
{
    private ?SessionData $sessionContainer = null;

    /** @var callable */
    private mixed $sessionLoader = null;

    public static function fromContainer(callable $sessionLoader): static
    {
        $instance = new static();
        $instance->sessionLoader = $sessionLoader;
        return $instance;
    }

    /**
     * @return void
     */
    #[\Override]
    public function setGroup(array $group, bool $clear = false): void
    {
        $this->getSession()->setGroup($group, $clear);
    }

    /** @psalm-api */
    public function writeData(): void
    {
        $this->getSession()->writeData();
    }

    /**
     * @return void
     */
    #[\Override]
    public function set(int|string $key, mixed $value, int|string|null $groupIndex = null): void
    {
        $this->getSession()->set($key, $value, $groupIndex);
    }

    #[\Override]
    public function get(int|string $key, int|string|null $groupIndex = null, mixed $default = null): mixed
    {
        return $this->getSession()->get($key, $groupIndex, $default);
    }

    #[\Override]
    public function getEntity(): mixed
    {
        return $this->getSession()->getEntity();
    }

    /**
     * @return void
     */
    #[\Override]
    public function remove(int|string $key, int|string|null $groupIndex = null): void
    {
        $this->getSession()->remove($key, $groupIndex);
    }

    /**
     * @return void
     */
    #[\Override]
    public function clear(): void
    {
        $this->getSession()->clear();
    }

    /** @psalm-api */
    public function restart(): void
    {
        $this->getSession()->restart();
    }

    /**
     * @return void
     */
    #[\Override]
    public function clearGroup(int|string|null $groupIndex = null): void
    {
        $this->getSession()->clearGroup($groupIndex);
    }

    #[\Override]
    public function has(int|string $key, int|string|null $groupIndex = null): ?bool
    {
        return $this->getSession()->has($key, $groupIndex);
    }

    #[\Override]
    public function isEmpty(): bool
    {
        return $this->getSession()->isEmpty();
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->getSession()->jsonSerialize();
    }

    private function getSession(): SessionData
    {
        if (!$this->sessionContainer) {
            $this->sessionContainer = $this->loadSession();
        }
        return $this->sessionContainer;
    }

    private function loadSession(): SessionData
    {
        if (!is_callable($this->sessionLoader)) {
            throw new \RuntimeException('Session loader is not set');
        }
        $session = ($this->sessionLoader)();
        if (!$session instanceof SessionData) {
            throw new \RuntimeException('Session loader must return SessionData');
        }
        return $session;
    }
}
