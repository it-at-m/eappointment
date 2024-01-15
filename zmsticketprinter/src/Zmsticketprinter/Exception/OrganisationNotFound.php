<?php

namespace BO\Zmsticketprinter\Exception;

class OrganisationNotFound extends \Exception
{
    protected $code = 404;

    protected $message = "To conseign a notification number, organisation id is required";
}
