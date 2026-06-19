<?php

namespace BO\Zmsbackend\Mail\Exception;

class ClientWithoutEmail extends \Exception
{
    protected $code = 404;

    protected $message = "No email found for client";
}
