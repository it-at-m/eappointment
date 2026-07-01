<?php

namespace BO\Zmsbackend\Organisation\Exception;

/**
 * class to generate an exception if children exists
 */
class OrganisationNotFound extends \Exception
{
    protected $code = 404;

    protected $message = 'Organisation id does not exists';
}
