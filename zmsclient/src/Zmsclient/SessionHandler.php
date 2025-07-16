<?php

namespace BO\Zmsclient;

/**
 * Session handler for mysql
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

    public static function getLastInstance(): ?self
    {
        return static::$lastInstance;
    }

    public function setHttpHandler(Http $http): void
    {
        $this->http = $http;
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function open($save_path, $name): bool
    {
        $this->sessionName = $name;
        return true;
    }

    public function close(): bool
    {
        return true;
    }

    public function read($sessionId, $params = []): string
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

    public function write($sessionId, $sessionData, $params = []): bool
    {
        $hashedSessionId = hash('sha256', $sessionId);
        $entity = new \BO\Zmsentities\Session();
        $entity->id = $hashedSessionId;
        $entity->name = $this->sessionName;
        $entity->content = unserialize($sessionData);

        try {
            $session = $this->http->readPostResult('/session/', $entity, $params)
                ->getEntity();
        } catch (Exception $exception) {
            if ($exception->getCode() == 404) {
                $session = null;
            }
            throw $exception;
        }

        return (null !== $session) ? true : false;
    }

    public function destroy($sessionId): bool
    {
        $hashedSessionId = hash('sha256', $sessionId);
        $result = $this->http->readDeleteResult('/session/' . $this->sessionName . '/' . $hashedSessionId . '/');
        return ($result) ? true : false;
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @SuppressWarnings(ShortMethodName)
     * @codeCoverageIgnore
     */
    public function gc(int $maxlifetime): bool
    {
        /*
         * $compareTs = time() - $maxlifetime;
         * $query = '
         * DELETE FROM
         * sessiondata
         * WHERE
         * UNIX_TIMESTAMP(`ts`) < ? AND
         * sessionname=?
         * ';
         * $statement = $this->getWriter()->prepare($query);
         * return $statement->execute(array(
         * $compareTs,
         * $this->sessionName
         * ));
         */
        return true;
    }
}
