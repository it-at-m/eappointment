<?php

namespace BO\Zmsapi\Exception;

/**
 * example class to generate an exception
 */
class FreeProcessListEmpty extends \BO\Zmsclient\Exception
{
    /**
     * @var String $template for rendering exception
     *
     */
    public $template = 'bo/zmsapi/exception/freeprocesslistempty';
}
