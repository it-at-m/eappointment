<?php

namespace BO\Zmsapi\Exception;

/**
 * example class to generate an exception
 */
class UnvalidSession extends \BO\Zmsclient\Exception
{
    /**
     * @var String $template for rendering exception
     *
     */
    public $template = 'bo/zmsapi/invalidsession';
}
