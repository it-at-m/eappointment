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
 */
class Request extends SlimRequest implements \Psr\Http\Message\ServerRequestInterface
{
    public function __construct($method = null, $uri = null, $body = 'php://memory', $headers = array())
    {
        $cookies = [];
        $serverParams = $_SERVER;
        $uploadedFiles = [];
        if (!$uri instanceof UriInterface) {
            $uri = new Uri($uri);
        }
        if (!$headers instanceof HeadersInterface) {
            $headers = new Headers($headers);
        }
        if (!$body instanceof StreamInterface) {
            if (!is_resource($body)) {
                $body = fopen($body, 'w+b');
            }
            $body = new Stream($body);
        }
        parent::__construct(
            $method,
            $uri,
            $headers,
            $cookies,
            $serverParams,
            $body,
            $uploadedFiles
        );
    }
}
