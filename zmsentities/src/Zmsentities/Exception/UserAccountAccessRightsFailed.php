<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class UserAccountAccessRightsFailed extends \Exception
{
    protected int $code = 403;

    protected string $message = 'Level of user rights are low, access rejected';

    public $templatedata = null;
}
