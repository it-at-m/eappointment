<?php

namespace BO\Zmsclient\Psr7;

/**
 * Layer to change PSR7 implementation if necessary
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Stream extends \Slim\Psr7\Stream implements \Psr\Http\Message\StreamInterface
{
    public function __construct(mixed $stream = null)
    {
        if (null === $stream) {
            $stream = fopen('php://memory', 'w+b');
        }
        if (!is_resource($stream)) {
            $stream = fopen(is_string($stream) ? $stream : 'php://memory', 'w+b');
        }
        if (!is_resource($stream)) {
            throw new \RuntimeException('Unable to open stream');
        }
        parent::__construct($stream);
    }
}
