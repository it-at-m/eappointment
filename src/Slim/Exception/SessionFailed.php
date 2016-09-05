<?php

namespace BO\Zmsapi\Exception;

/**
 * example class to generate an exception
 */
class SessionFailed extends \Exception
{
    public $template = 'bo/slim/exception/sessionfailed.twig';
}
