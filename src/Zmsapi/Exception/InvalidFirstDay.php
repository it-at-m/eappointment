<?php

namespace BO\Zmsapi\Exception;

/**
 * example class to generate an exception
 */
class InvalidFirstDay extends \BO\Zmsclient\Exception
{
    /**
     * @var String $template for rendering exception
     *
     */
    public $template = 'bo/zmsapi/invalidfirstday';
}
