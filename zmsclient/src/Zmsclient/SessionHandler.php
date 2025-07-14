<?php

namespace BO\Zmsclient;

/**
 * Session handler for mysql
 * @SuppressWarnings(PHPMD.ShortMethodName)
 */
class SessionHandler implements \SessionHandlerInterface
{
    public $sessionName;

    /**
     * Adds a parameter "sync" on reading the session from the API
     * Use a value of 1 to enable synchronous reads
     * if a former session write happened during a redirect
     */
    public static $useSyncFlag = 0;

    protected static $lastInstance = null;

    /**
     * @var \BO\Zmsclient\Http $http
     *
     */
    protected $http = null;


    public function __construct(Http $http)
    {
        $this->setHttpHandler($http);
        static::$lastInstance = $this;
    }

    public static function getLastInstance()
    {
        return static::$lastInstance;
    }

    public function setHttpHandler(Http $http)
    {
        $this->http = $http;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function open(string $save_path, string $name): bool
    {
        $this->sessionName = $name;
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read(string $sessionId): string
    {
        $hashedSessionId = hash('sha256', $sessionId);
        $params['sync'] = static::$useSyncFlag;
        try {
            $session = $this->http->readGetResult(
                '/session/' . $this->sessionName . '/' . $hashedSessionId . '/',
                $params
            )
            ->getEntity();
        } catch (Exception\ApiFailed $exception) {
            throw $exception;
        } catch (Exception $exception) {
            if ($exception->getCode() == 404) {
                $session = false;
            } else {
                throw $exception;
            }
        }
        if (isset($params['oidc']) && 1 == $params['oidc'] && $session) {
            $session = $session->withOidcDataOnly();
        }
        return ($session && isset($session['content'])) ? serialize($session->getContent()) : '';
    }

    public function write(string $sessionId, string $sessionData): bool
    {
        $hashedSessionId = hash('sha256', $sessionId);
        $entity = new \BO\Zmsentities\Session();
        $entity->id = $hashedSessionId;
        $entity->name = $this->sessionName;
        $entity->content = unserialize($sessionData);

        try {
            $session = $this->http->readPostResult('/session/', $entity)
                ->getEntity();
        } catch (Exception $exception) {
            if ($exception->getCode() == 404) {
                $session = null;
            }
            throw $exception;
        }

        return (null !== $session) ? true : false;
    }

    public function destroy(string $sessionId): bool
    {
        $hashedSessionId = hash('sha256', $sessionId);
        $result = $this->http->readDeleteResult('/session/' . $this->sessionName . '/' . $hashedSessionId . '/');
        return ($result) ? true : false;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortMethodName)
     */
    public function gc(int $maxlifetime): int|false
    {
        // No-op for now
        return 0;
    }
}
