<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class TemplateNotFound extends \Exception
{
    protected $code = 500;

    protected $message = 'The requested template does not exist';
}
