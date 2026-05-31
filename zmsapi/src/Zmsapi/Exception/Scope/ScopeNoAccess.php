<?php

namespace BO\Zmsapi\Exception\Scope;

/**
 * example class to generate an exception
 */
class ScopeNoAccess extends \Exception
{
    protected int $code = 403;

    protected string $message = 'Ihre aktuelle Anmeldung hat keine Rechte diesen Standort zu ändern.';
}
