<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class ScopeMissingProvider extends \Exception
{
    protected $code = 500;

    public $data = [];
}
