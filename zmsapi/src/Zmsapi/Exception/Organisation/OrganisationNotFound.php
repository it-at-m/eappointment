<?php

namespace BO\Zmsapi\Exception\Organisation;

/**
 * class to generate an exception if children exists
 */
class OrganisationNotFound extends \Exception
{
    protected int $code = 404;

    protected string $message = 'Organisation id does not exists';
}
