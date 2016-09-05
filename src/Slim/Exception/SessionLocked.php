<?php

namespace BO\Zmsapi\Exception;

/**
 * example class to generate an exception
 */
class SessionLocked extends \Exception
{
    public $template = 'bo/slim/exception/sessionlocked.twig';
}
