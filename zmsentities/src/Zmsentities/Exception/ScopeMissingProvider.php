<?php

namespace BO\Zmsentities\Exception;

/**
 * example class to generate an exception
 */
class ScopeMissingProvider extends \Exception
{
    protected int $code = 500;

    /**
     * @var array
     */
    public array $data = [];
}
