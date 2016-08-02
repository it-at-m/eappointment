<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Request extends \Asika\Http\ServerRequest implements \Psr\Http\Message\RequestInterface
{
    public function __construct($method = null, $uri = null, $body = 'php://memory', $headers = array())
    {
        parent::__construct([], [], $uri, $method, $body, $headers);
    }
}
