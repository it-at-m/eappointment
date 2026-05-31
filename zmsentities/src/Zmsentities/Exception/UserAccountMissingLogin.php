<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class UserAccountMissingLogin extends \Exception
{
    protected int $code = 401;
}
