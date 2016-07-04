<?php

namespace BO\Zmsclient;

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
     * @var Psr7\Uri $uri
     */
    protected $uri = null;

    /**
     *
     * @param Psr7\ClientInterface $client
     */
    public function __construct($baseUrl, Psr7\ClientInterface $client = null)
    {
        $this->http_baseurl = parse_url($baseUrl, PHP_URL_PATH);
        $this->uri = new Psr7\Uri();
        $this->uri = $this->uri->withScheme(parse_url($baseUrl, PHP_URL_SCHEME));
        $this->uri = $this->uri->withHost(parse_url($baseUrl, PHP_URL_HOST));
        $port = parse_url($baseUrl, PHP_URL_PORT);
        if ($port) {
            $this->uri = $this->uri->withPort($port);
        }
        $user = parse_url($baseUrl, PHP_URL_USER);
        $pass = parse_url($baseUrl, PHP_URL_PASS);
        if ($user) {
            $this->uri = $this->uri->withUserInfo($user, $pass);
        }
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
        $xAuthKey = Auth::getKey();
        if (null !== $xAuthKey) {
            $request = $request->withHeader('X-Authkey', $xAuthKey);
        }
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
    public function readGetResult($relativeUrl, array $getParameters = null)
    {
        $uri = $this->uri->withPath($this->http_baseurl . $relativeUrl);
        if (null !== $getParameters) {
            $uri = $uri->withQuery(http_build_query($getParameters));
        }
        $request = new Psr7\Request('GET', $uri);
        $response = $this->readResponse($request);
        return new Result($response, $request);
    }

    /**
     * Creates a POST-Http-Request and fetches the response
     *
     * @param String $relativeUrl
     * @param \BO\Zmsentities\Schema\Entity $entity
     * @param Array $getParameters (optional)
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readPostResult($relativeUrl, \BO\Zmsentities\Schema\Entity $entity, array $getParameters = null)
    {
        $uri = $this->uri->withPath($this->http_baseurl . $relativeUrl);
        if (null !== $getParameters) {
            $uri = $uri->withQuery(http_build_query($getParameters));
        }
        $request = new Psr7\Request('POST', $uri);
        $body = new Psr7\Stream();
        $body->write(json_encode($entity));
        $request = $request->withBody($body);
        $response = $this->readResponse($request);
        return new Result($response, $request);
    }

    /**
     * Creates a DELETE-Http-Request and fetches the response
     *
     * @param String $relativeUrl
     * @param Array $getParameters (optional)
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readDeleteResult($relativeUrl, array $getParameters = null)
    {
        $uri = $this->uri->withPath($this->http_baseurl . $relativeUrl);
        if (null !== $getParameters) {
            $uri = $uri->withQuery(http_build_query($getParameters));
        }
        $request = new Psr7\Request('DELETE', $uri);
        $response = $this->readResponse($request);
        return new Result($response, $request);
    }
}
