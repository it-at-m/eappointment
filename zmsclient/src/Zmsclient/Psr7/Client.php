<?php

namespace BO\Zmsclient\Psr7;

use Jgut\Spiral\Client as Transport;
use Jgut\Spiral\Transport\Curl as Curl;
use BO\Zmsclient\Exception\ClientCreationException;
use BO\Zmsclient\Psr17\ResponseFactory;
use Exception;
use Slim\Psr7\Factory\StreamFactory;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

class Client
{

    /**
     * @var array $curlopt List of curl options like [CURLOPT_TIMEOUT => 10]
     *      defined with each component's bootstrap.php
     */
    public static $curlopt = [];

    /**
     * @param RequestInterface $request
     * @param array $curlOptions Additional or special curl options
     *
     * @return ResponseInterface
     * @throws RequestException|ClientCreationException|ClientExceptionInterface
     */
    public static function readResponse(RequestInterface $request, array $curlOptions = array()): ResponseInterface
    {
        $client = static::getCurlClient($curlOptions);

        try {
            return $client->sendRequest($request);
        } catch (Exception $exception) {
            throw new RequestException($exception->getMessage(), $request, $exception);
        }
    }

    /**
     * @param $curlOptions
     * @return \Http\Client\Curl\Client
     * @throws ClientCreationException
     */
    public static function getCurlClient($curlOptions): ClientInterface
    {
        $curlOptions = $curlOptions + static::$curlopt;
        if (!isset($curlOptions[CURLOPT_USERAGENT])) {
            $curlOptions[CURLOPT_USERAGENT] =
                'Client' . (defined("\App::IDENTIFIER") ? constant("\App::IDENTIFIER") : 'ZMS');
        }

        try {
            $client = new \Http\Client\Curl\Client(
                new ResponseFactory(),
                new StreamFactory(),
                $curlOptions
            );
        } catch (InvalidArgumentException $exception) {
            throw new ClientCreationException($exception->getMessage(), 0, $exception);
        }

        return $client;
    }

    public function send(RequestInterface $request)
    {
        return static::readResponse($request);
    }
}
