<?php

namespace BO\Zmsdb\Exception\Mail;

class ClientWithoutEmail extends \Exception
{
    protected int $code = 404;

    protected string $message = "No email found for client";
}
