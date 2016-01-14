<?php

namespace BO\Zmsclient;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

/**
 * Access api method_exists
 */
class Http
{
    /**
     * @var Psr7\ClientInterface $client
     */
    protected $client = null;

    /**
     * @var String $http_username
     */
    protected $http_username = null;

    /**
     * @var String $http_password
     */
    protected $http_password = null;

    /**
     * @var String $http_baseurl
     */
    protected $http_baseurl = null;

    /**
     *
     * @param Psr7\ClientInterface $client
     */
    public function __construct($baseUrl, Psr7\ClientInterface $client = null)
    {
        $this->http_baseurl = $baseUrl;
        if (null === $client) {
            $client = new Psr7\Client();
        }
        $this->client = $client;
    }

    /**
     * Start request and fetch response
     * The request is extended by auth informations
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(\Psr\Http\Message\RequestInterface $request)
    {
        $request = $this->getAuthorizedRequest($request);
        return $this->client->readResponse($request);
    }

    /**
     * Extend the request by auth informations
     *
     * @param \Psr\Http\Message\RequestInterface $request
     *
     * @return \Psr\Http\Message\RequestInterface
     */
    public function getAuthorizedRequest(\Psr\Http\Message\RequestInterface $request)
    {
        // @todo implement authorization
        return $request;
    }

    /**
     * Creates a GET-Http-Request and fetches the response
     *
     * @param String $relativeUrl
     * @param Array $getParameters (optional)
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readGetResult($relativeUrl, Array $getParameters = null)
    {
        $uri = new Uri();
        $uri = $uri->withPath($this->http_baseurl . $relativeUrl);
        if (null !== $getParameters) {
            $uri = $uri->withQuery(http_build_query($getParameters));
        }
        $request = new Request('GET', $uri);
        $response = $this->readResponse($request);
        return new Result($response);
    }
}
