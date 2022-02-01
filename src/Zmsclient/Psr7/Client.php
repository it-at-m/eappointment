<?php

namespace BO\Zmsclient\Psr7;

use Sunrise\Http\Client\Curl\Client as Transport;

class Client implements ClientInterface
{

    /**
     * @var Array $curlopt List of curl options like [CURLOPT_TIMEOUT => 10]
     */
    public static $curlopt = [];

    protected static $curlClient = null;

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param Array $curlopts Additional or special curl options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function readResponse(\Psr\Http\Message\RequestInterface $request, array $curlopts = array())
    {
        $client = static::getClient($curlopts);
        try {
            return $client->sendRequest($request);
        } catch (\Exception $exception) {
            throw new RequestException($exception->getMessage(), $request);
        }
    }

    public static function getClient($curlopts)
    {
        $curlopts = $curlopts + static::$curlopt;
        if (!isset($curlopts[CURLOPT_USERAGENT])) {
            $curlopts[CURLOPT_USERAGENT] =
                'Client' . (defined("\App::IDENTIFIER") ? constant("\App::IDENTIFIER") : 'ZMS');
        }
        if (null === static::$curlClient) {
            static::$curlClient = new Transport(new Response(), $curlopts);
        }
        return static::$curlClient;
    }
}
