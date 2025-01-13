<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsclient\Exception;

use Throwable;
use Exception;

class ClientCreationException extends Exception
{
    protected $code = 500;

    protected $message = 'An Exception with the cURL Client creation occurred.';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $code = $code === 0 ? $this->code : $code;

        parent::__construct($this->message . ' ' . $message, $code, $previous);
    }
}
