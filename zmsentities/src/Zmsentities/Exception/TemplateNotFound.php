<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class TemplateNotFound extends \Exception
{
    protected int $code = 500;

    protected string $message = 'The requested template does not exist';

    public $data;
}
