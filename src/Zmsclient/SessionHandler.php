<?php
namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class SessionHandler implements \SessionHandlerInterface
{

    public $sessionName;

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
    }

    public function close()
    {
        return true;
    }

    public function read($sessionId)
    {
        try {
            $session = $this->http->readGetResult('/session/' . $this->sessionName . '/' . $sessionId . '/')
                ->getEntity();
        } catch (Exception\ApiFailed $exception) {
            // @codeCoverageIgnoreStart
            throw $exception;
            // @codeCoverageIgnoreEnd
        } catch (Exception $exception) {
            if ($exception->getCode() == 404) {
                $session = null;
            } else {
                // @codeCoverageIgnoreStart
                throw $exception;
                // @codeCoverageIgnoreEnd
            }
        }
        return (isset($session) && array_key_exists('content', $session)) ? $session['content'] : null;
    }

    public function write($sessionId, $sessionData)
    {
        $entity = new \BO\Zmsentities\Session();
        $entity->id = $sessionId;
        $entity->name = $this->sessionName;
        $entity->content = $sessionData;

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
    }
}
