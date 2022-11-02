<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsadmin\Exception;

class BadRequest extends \Exception
{
    protected $code = 400;

    protected $message = 'The request body was empty or not having the right format.';
}
