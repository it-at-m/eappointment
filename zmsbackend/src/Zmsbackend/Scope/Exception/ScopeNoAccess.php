<?php

namespace BO\Zmsbackend\Scope\Exception;

/**
 * example class to generate an exception
 */
class ScopeNoAccess extends \Exception
{
    protected $code = 403;

    protected $message = 'Ihre aktuelle Anmeldung hat keine Rechte diesen Standort zu ändern.';
}
