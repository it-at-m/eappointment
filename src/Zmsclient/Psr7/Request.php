<?php

namespace BO\Zmsclient\Psr7;

use Slim\Interfaces\Http\HeadersInterface;
use Slim\Http\Headers;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Layer to change PSR7 implementation if necessary
 */
class Request extends \Slim\Http\Request implements \Psr\Http\Message\ServerRequestInterface
{
    public function __construct($method = null, $uri = null, $body = 'php://memory', $headers = array())
    {
        $cookies = [];
        $serverParams = [];
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
