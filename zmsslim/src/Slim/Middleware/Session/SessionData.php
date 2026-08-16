<?php

namespace BO\Slim\Middleware\Session;

use Psr\Http\Message\ServerRequestInterface as Request;

class SessionData implements SessionInterface
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var boolean
     */
    private $isLocked = false;


    protected $entityClass = null;

    /**
     * __construct is not allowed, use
     * - {@see SessionData::getSessionFromName}
     * instead
     */
    public function __construct($data = null)
    {
        $this->data = $data;
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     * @SuppressWarnings(Unused)
     *
     * @return self
     */
    public static function getSession(Request $request)
    {
        if (headers_sent() === false && session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
            if (!count($_SESSION)) {
                $_SESSION['status'] = 'start';
            };
            $session = $_SESSION;
        } else {
            throw  new \BO\Slim\Exception\SessionFailed("Headers sent or a session is already active");
        }

        $instance = new self();
        $instance->data = $session;
        return $instance;
    }

    public function writeData(): void
    {
        session_write_close();
        $this->isLocked = true;
    }

    /**
     * @return void
     */
    #[\Override]
    public function setGroup(array $group, bool $clear = false): void
    {
        foreach ($group as $index => $items) {
            if ($clear) {
                $this->clearGroup($index);
            }
            if (is_array($items) && 0 < count($items)) {
                foreach ($items as $key => $value) {
                    $this->set($key, $value, $index);
                }
            }
        }
    }

    /**
     * @SuppressWarnings(Superglobals)
     *
     * @return array
     *
     * @param (int|string) $key
     * @param (int|string)|null $groupIndex
     *
     */
    #[\Override]
    public function set(int|string $key, mixed $value, int|string|null $groupIndex = null): void
    {
        if (null === $groupIndex) {
            $this->data[$key] = self::convertValueToScalar($value);
        } else {
            $this->data[$groupIndex][$key] = self::convertValueToScalar($value);
        }
        if ($this->isLocked) {
            throw new \BO\Slim\Exception\SessionLocked();
        }
        $_SESSION = $this->data;
    }

    #[\Override]
    public function get(int|string $key, int|string|null $groupIndex = null, mixed $default = null): mixed
    {
        if (! $this->has($key, $groupIndex)) {
            return self::convertValueToScalar($default);
        } elseif (null === $groupIndex) {
            return $this->data[$key];
        } else {
            return $this->data[$groupIndex][$key];
        }
    }

    #[\Override]
    public function getEntity(): mixed
    {
        if (null === $this->entityClass) {
            throw new \Exception("Entity-Class not set");
        }
        $sessionContent = clone $this->entityClass;
        $sessionContent->content = $this->data;
        return $sessionContent;
    }

    public function setEntityClass($entityClass): static
    {
        $this->entityClass = $entityClass;
        return $this;
    }

    /**
     * @return void
     */
    #[\Override]
    public function remove(int|string $key, int|string|null $groupIndex = null): void
    {
        if (null === $groupIndex) {
            unset($this->data[$key]);
        } else {
            unset($this->data[$groupIndex][$key]);
        }
    }

    /**
     * @SuppressWarnings(Superglobals)
     *
     * @return self
     *
     * @param (int|string)|null $groupIndex
     *
     */
    #[\Override]
    public function clearGroup(int|string|null $groupIndex = null): void
    {
        if (null !== $groupIndex) {
            $this->data[$groupIndex] = [];
            $_SESSION = $this->data;
        }
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     * @return self
     */
    #[\Override]
    public function clear(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            setcookie(session_name(), '', time() - 3600, '/');
            $_SESSION = array();
            session_destroy();
        }
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     * @return self
     */
    public function restart()
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
            $_SESSION = array();
            $this->data = $_SESSION;
        }
    }

    /**
     * @return bool|null
     */
    #[\Override]
    public function has(int|string $key, int|string|null $groupIndex = null): ?bool
    {
        if (null === $groupIndex) {
            return array_key_exists($key, $this->data);
        } else {
            if (array_key_exists($groupIndex, $this->data)) {
                return array_key_exists($key, $this->data[$groupIndex]);
            }
        }
    }

    /**
     * @return bool
     */
    #[\Override]
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    // TODO: Review the use of `mixed` return type.
    // This method delegates to getSession()->jsonSerialize(), which may return various types.
    // Consider adding stricter return typing if getSession() can be more precisely typed.
    #[\Override]
    public function jsonSerialize(): mixed
    {
        return json_encode($this->data);
    }

    private static function convertValueToScalar($value)
    {
        return json_decode(json_encode($value), true);
    }
}
