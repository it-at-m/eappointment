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
    #[\Override]
    public function open(string $path, string $name): bool
    {
        $this->sessionName = $name;
        return true;
    }

    #[\Override]
    public function close(): bool
    {
        return true;
    }

    #[\Override]
    public function read(string $id, array $params = []): string
    {
        $hashedSessionId = hash('sha256', $id);
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

    #[\Override]
    public function write(string $id, string $data, array $params = []): bool
    {
        $hashedSessionId = hash('sha256', $id);
        $entity = new \BO\Zmsentities\Session();
        $entity->id = $hashedSessionId;
        $entity->name = $this->sessionName;
        $entity->content = unserialize($data);

        $session = $this->http->readPostResult('/session/', $entity, $params)
            ->getEntity();

        return (null !== $session) ? true : false;
    }

    #[\Override]
    public function destroy(string $id): bool
    {
        $hashedSessionId = hash('sha256', $id);
        $result = $this->http->readDeleteResult('/session/' . $this->sessionName . '/' . $hashedSessionId . '/');
        return ($result) ? true : false;
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @SuppressWarnings(ShortMethodName)
     * @codeCoverageIgnore
     */
    #[\Override]
    public function gc(int $max_lifetime): int|false
    {
        /*
         * $compareTs = time() - $max_lifetime;
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
        return 1;
    }
}
