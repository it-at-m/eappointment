<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Stream extends \Slim\Psr7\Stream implements \Psr\Http\Message\StreamInterface
{
    public function __construct($stream = null)
    {
        $stream = (null === $stream) ? fopen('php://memory', 'w+b') : $stream;
        $stream = (!is_resource($stream)) ? fopen($stream, 'w+b') : $stream;
        parent::__construct($stream);
    }
}
