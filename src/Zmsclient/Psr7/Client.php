<?php

namespace BO\Zmsclient\Psr7;

use \Asika\Http\Transport\CurlTransport as Curl;

class Client implements ClientInterface
{

    /**
     * @var Array $curlopt List of curl options like [CURLOPT_TIMEOUT => 10]
     */
    static public $curlopt = [];

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function readResponse(\Psr\Http\Message\RequestInterface $request)
    {
        return self::getTransport()->request($request);
    }

    protected static function getTransport()
    {
        $transport = new Curl();
        $transport->setOption('options', self::$curlopt);
        return $transport;
    }
}
