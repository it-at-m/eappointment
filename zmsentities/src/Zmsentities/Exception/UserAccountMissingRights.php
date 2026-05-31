<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class UserAccountMissingRights extends \Exception
{
    protected int $code = 403;

    public $templatedata = null;
}
