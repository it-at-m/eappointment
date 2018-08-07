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

    /**
     * @var \BO\Zmsclient\Http $http
     *
     */
    protected $http = null;


    public function __construct(Http $http)
    {
        $this->http = $http;
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function open($save_path, $name)
    {
        $this->sessionName = $name;
        return true;
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        try {
            $session = $this->http->readGetResult(
                '/session/' . $this->sessionName . '/' . $sessionId . '/',
                ['sync' => static::$useSyncFlag]
            )
            ->getEntity();
        } catch (Exception\ApiFailed $exception) {
            // @codeCoverageIgnoreStart
            throw $exception;
            // @codeCoverageIgnoreEnd
        } catch (Exception $exception) {
            if ($exception->getCode() == 404) {
                $session = false;
            } else {
                // @codeCoverageIgnoreStart
                throw $exception;
                // @codeCoverageIgnoreEnd
            }
        }
        return ($session && array_key_exists('content', $session)) ? serialize($session->getContent()) : '';
    }

    public function write($sessionId, $sessionData)
    {
        $entity = new \BO\Zmsentities\Session();
        $entity->id = $sessionId;
        $entity->name = $this->sessionName;
        $entity->content = unserialize($sessionData);

        try {
            $session = $this->http->readPostResult('/session/', $entity)
                ->getEntity();
        } catch (Exception $exception) {
            // @codeCoverageIgnoreStart
            if ($exception->getCode() == 404) {
                $session = null;
            }
            // @codeCoverageIgnoreEnd
            throw $exception;
        }

        return (null !== $session) ? true : false;
    }

    public function destroy($sessionId)
    {
        $result = $this->http->readDeleteResult('/session/' . $this->sessionName . '/' . $sessionId . '/');
        return ($result) ? true : false;
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @SuppressWarnings(ShortMethodName)
     * @codeCoverageIgnore
     */
    public function gc($maxlifetime)
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
