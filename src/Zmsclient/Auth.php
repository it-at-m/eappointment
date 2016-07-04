<?php
namespace BO\Zmsclient;

/**
 * Session handler for mysql
 */
class Auth
{
    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function setKey($authKey)
    {
        $_SESSION = array(
            'X-Authkey' => $authKey
        );
    }

    /**
     *
     * @SuppressWarnings(Superglobals)
     *
     */
    public static function getKey()
    {
        return (isset($_SESSION['X-Authkey'])) ? $_SESSION['X-Authkey'] : null;
    }
}
