<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Stream extends \Slim\Http\Stream implements \Psr\Http\Message\StreamInterface
{
    public function __construct($stream = null)
    {
        if (null === $stream) {
            $stream = fopen('php://memory', 'w+b');
        } elseif (!is_resource($stream)) {
            $stream = fopen($stream, 'w+b');
        }
        parent::__construct($stream);
    }
}
