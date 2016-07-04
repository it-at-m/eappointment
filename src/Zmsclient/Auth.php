<?php
namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class Auth
{
    public static function setKey($authKey, $sessionName)
    {
        self::startSession($sessionName);
        self::writeContentToSession(array(
            'content' => array(
                'X-Authkey' => $authKey
            )
        ));
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getKey()
    {
        self::startSession();
        $session = new \BO\Zmsentities\Session($_SESSION);
        return ('' != $session->content['X-Authkey']) ? $session->content['X-Authkey'] : null;
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function deleteKey()
    {
        self::startSession();
        session_destroy();
    }

    private static function startSession($sessionName = '')
    {
        if (headers_sent() === false && session_status() !== PHP_SESSION_ACTIVE) {
            $handler = new SessionHandler();
            session_set_save_handler($handler, true);
            if ('' != $sessionName) {
                session_name($sessionName);
            }
            session_start();
        }
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     * @return self
     */
    private static function writeContentToSession($sessionData = null)
    {
        $_SESSION = $sessionData;
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_register_shutdown();
        }
    }
}
