<?php

namespace BO\Zmsclient\Psr7;

use Jgut\Spiral\Client as Transportclient;
use Jgut\Spiral\Transport\Curl;
use GuzzleHttp\Psr7\Response;

class Client
{

    /**
     * @var Array $curlopt List of curl options like [CURLOPT_TIMEOUT => 10]
     */
    static public $curlopt = [];

    public static function request(\Psr\Http\Message\RequestInterface $request)
    {
        return self::getTransport()->request($request, new Response());
    }

    protected static function getTransport()
    {
        $transport = new Curl();
        foreach (self::$curlopt as $option => $value) {
            $transport->setOption($option, $value);
        }
        $client = new Transportclient($transport);
        return $client;
    }
}
