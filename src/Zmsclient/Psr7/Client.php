<?php

namespace BO\Zmsclient\Psr7;

use Jgut\Spiral\Client as Transport;
use Jgut\Spiral\Transport\Curl as Curl;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;

class Client implements ClientInterface
{

    /**
     * @var Array $curlopt List of curl options like [CURLOPT_TIMEOUT => 10]
     */
    public static $curlopt = [];

    protected static $curlClient = null;

    /**
     * @param RequestInterface $request
     * @param Array $curlopts Additional or special curl options
     *
     * @return ResponseInterface
     */
    public static function readResponse(RequestInterface $request, array $curlopts = array())
    {
        $transport = static::getClient($curlopts);
        try {
            return $transport->request($request, new Response());
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
            $curl = Curl::createFromDefaults();
            $curl->setOptions($curlopts);
            $transport = new Transport($curl);
            static::$curlClient = $transport;
        }
        return static::$curlClient;
    }

    public function send(RequestInterface $request)
    {
        return static::readResponse($request);
    }
}
