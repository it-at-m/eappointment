<?php

namespace BO\Zmsclient\Psr7;

use Jgut\Spiral\Client as Transport;
use Jgut\Spiral\Transport\Curl as Curl;

class Client implements ClientInterface
{

    /**
     * @var Array $curlopt List of curl options like [CURLOPT_TIMEOUT => 10]
     */
    static public $curlopt = [];

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @param Array $curlopts Additional or special curl options
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public static function readResponse(\Psr\Http\Message\RequestInterface $request, array $curlopts = array())
    {
        $curlopts = $curlopts + self::$curlopt;
        $curl = Curl::createFromDefaults();
        $curl->setOptions(static::$curlopt);
        $transport = new Transport($curl);
        try {
            return $transport->request($request, new Response());
        } catch (\Exception $exception) {
            throw new RequestException($exception->getMessage(), $request);
        }
    }
}
