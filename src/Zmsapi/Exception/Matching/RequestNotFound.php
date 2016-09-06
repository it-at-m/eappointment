<?php

namespace BO\Zmsapi\Exception\Matching;

/**
 * example class to generate an exception
 */
class RequestNotFound extends \Exception
{
    /**
     * @var String $template for rendering exception
     *
     */
    public $template = 'bo/zmsapi/exception/matching/matchingfailed/';
}
