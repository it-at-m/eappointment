<?php

namespace BO\Zmsbackend\Status\Exception;

/**
 * Thrown when /status/ is called without a valid workstation login or X-Token.
 */
class StatusAuthentificationFailed extends \Exception
{
    protected $code = 401;

    protected $message = 'Authentification failed - access to status not granted';
}
