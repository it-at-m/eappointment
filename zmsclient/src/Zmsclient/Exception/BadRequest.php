<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsclient\Exception;

class BadRequest extends \BO\Zmsclient\Exception
{
    protected $code = 400;
}
