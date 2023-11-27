<?php

namespace BO\Zmsdb\Exception\Mail;

class ClientWithoutEmail extends \Exception
{
    protected $code = 404;

    protected $message = "No email found for client";
}
