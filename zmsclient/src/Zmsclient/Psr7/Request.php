<?php

namespace BO\Zmsclient\Psr7;

use Slim\Psr7\Interfaces\HeadersInterface;
use Slim\Psr7\Headers;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;
use BO\Slim\Request as SlimRequest;

/**
 * Layer to change PSR7 implementation if necessary
 * @SuppressWarnings(Superglobals)
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Request extends SlimRequest implements \Psr\Http\Message\ServerRequestInterface
{
    public function __construct(
        ?string $method = null,
        mixed $uri = null,
        mixed $body = 'php://memory',
        mixed $headers = array()
    ) {
        $cookies = [];
        $serverParams = $_SERVER;
        $uploadedFiles = [];
        if (!$uri instanceof UriInterface) {
            $uri = new Uri(is_string($uri) ? $uri : '');
        }
        if (!$headers instanceof HeadersInterface) {
            $headers = new Headers(is_array($headers) ? $headers : []);
        }
        if (!$body instanceof StreamInterface) {
            if (!is_resource($body)) {
                $opened = fopen(is_string($body) ? $body : 'php://memory', 'w+b');
                if ($opened === false) {
                    throw new \RuntimeException('Unable to open request body stream');
                }
                $body = $opened;
            }
            $body = new Stream($body);
        }
        parent::__construct(
            $method ?? 'GET',
            $uri,
            $headers,
            $cookies,
            $serverParams,
            $body,
            $uploadedFiles
        );
    }
}
