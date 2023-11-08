<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class UserAccountAccessRightsFailed extends \Exception
{
    protected $code = 403;

    protected $message = 'Level of user rights are low, access rejected';
}
