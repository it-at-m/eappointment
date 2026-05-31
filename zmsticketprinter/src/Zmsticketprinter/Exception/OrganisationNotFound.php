<?php

namespace BO\Zmsticketprinter\Exception;

class OrganisationNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = "To conseign a notification number, organisation id is required";
}
