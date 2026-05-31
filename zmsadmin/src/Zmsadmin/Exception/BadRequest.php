<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsadmin\Exception;

class BadRequest extends \Exception
{
    protected int $code = 400;

    protected string $message = 'The request body was empty or not having the right format.';
}
