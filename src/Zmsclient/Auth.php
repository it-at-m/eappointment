<?php
namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class Auth
{

    / /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setKey($authKey, $sessionName)
    {
        if (headers_sent() === false && session_status() !== PHP_SESSION_ACTIVE) {
            $handler = new SessionHandler();
            session_set_save_handler($handler, true);
            session_name($sessionName);
            session_start();
        }
        $_SESSION = array('authKey' => $authKey);
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getKey()
    {
        $session = new \BO\Zmsentities\Session($_SESSION);
        return $session->content['authKey'];
    }
}
