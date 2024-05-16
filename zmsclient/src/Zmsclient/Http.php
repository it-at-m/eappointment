<?php

namespace BO\Zmsclient;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;
use Slim\Psr7\Headers;

/**
 * Adapter & Decorator Class to use the Psr7\Client
 *
 * Access api method_exists
 * @SuppressWarnings(Coupling)
 */
class Http
{
    /**
     * @var Psr7\ClientInterface|null
     */
    protected $client = null;

    /**
     * @var string
     */
    protected $http_baseurl = '';

    /**
     * @var bool
     * with authentification request if true
     */
    public static $authEnabled = true;

    /**
     * @var Psr7\Uri|null
     */
    protected $uri = null;

    /**
     * @var bool
     * Log requests and responses if true
     */
    public static $logEnabled = true;

    /**
     * @var array
     * Contains a list of requests and responses if logging is enabled
     */
    public static $log = [];

    /**
     * @var string|null
     */
    protected $apikeyString = null;

    /**
     * @var string|null
     */
    protected $workflowkeyString = null;

    /**
     * @var int|null
     */
    public static $jsonCompressLevel = null;

    /**
     *
     * @param Psr7\ClientInterface $client
     */
    public function __construct($baseUrl, Psr7\ClientInterface $client = null)
    {
        $this->http_baseurl = parse_url($baseUrl, PHP_URL_PATH) ?? '';
        $this->uri = new Psr7\Uri();
        $this->uri = $this->uri->withScheme(parse_url($baseUrl, PHP_URL_SCHEME) ?? '');
        $this->uri = $this->uri->withHost(parse_url($baseUrl, PHP_URL_HOST) ?? '');
        $port = parse_url($baseUrl, PHP_URL_PORT);
        if ($port) {
            $this->uri = $this->uri->withPort($port);
        }
        $user = parse_url($baseUrl, PHP_URL_USER);
        $pass = parse_url($baseUrl, PHP_URL_PASS);
        if ($user) {
            $this->setUserInfo($user, $pass);
        }
        if (null === $client) {
            $client = new Psr7\Client();
        }
        $this->client = $client;
    }

    public function setUserInfo($user, $pass)
    {
        $this->uri = $this->uri->withUserInfo($user, $pass);
        return $this;
    }

    public function getUserInfo()
    {
        return $this->uri->getUserInfo();
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
        if (static::$authEnabled) {
            $request = $this->getAuthorizedRequest($request);
        }
        if (null !== static::$jsonCompressLevel) {
            $request = $request->withHeader('X-JsonCompressLevel', static::$jsonCompressLevel);
        }
        $startTime = microtime(true);
        $response = $this->client->readResponse($request);
        if (self::$logEnabled) {
            self::$log[] = $request;
            self::$log[] = $response;
            $responseSizeKb = round(strlen($response->getBody()->getContents()) / 1024);
            self::$log[] = "Response ($responseSizeKb kb) time in s: " . round(microtime(true) - $startTime, 3);
        }
        return $response;
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
        $userInfo = $request->getUri()->getUserInfo();
        $xAuthKey = Auth::getKey();
        if (null !== $xAuthKey && ! $userInfo) {
            $request = $request->withHeader('X-Authkey', $xAuthKey);
        } elseif ($userInfo) {
            $request = $request->withHeader('Authorization', 'Basic '. base64_encode($userInfo));
        }
        if (null !== $this->apikeyString) {
            $request = $request->withHeader('X-Api-Key', $this->apikeyString);
        }
        if (null !== $this->workflowkeyString) {
            $request = $request->withHeader('X-Workflow-Key', $this->workflowkeyString);
        }

        return $request;
    }

    public function setApiKey($apikeyString)
    {
        $this->apikeyString = $apikeyString;
        return $this;
    }

    public function setWorkflowKey($apikeyString)
    {
        $this->workflowkeyString = $apikeyString;
        return $this;
    }

    /**
     * Creates a GET-Http-Request and fetches the response
     *
     * @param String $relativeUrl
     * @param array|null $getParameters (optional)
     *
     * @return Result
     */
    public function readGetResult($relativeUrl, array $getParameters = null, $xToken = null)
    {
        $uri = $this->uri->withPath($this->http_baseurl . $relativeUrl);
        if (null !== $getParameters) {
            $uri = $uri->withQuery(http_build_query($getParameters));
        }
        $request = self::createRequest('GET', $uri);

        if (null !== $xToken) {
            $request = $request->withHeader('X-Token', $xToken);
        }

        return $this->readResult($request);
    }

    /**
     * Creates a POST-Http-Request and fetches the response
     *
     * @param String $relativeUrl
     * @param \BO\Zmsentities\Schema\Entity $entity
     * @param Array $getParameters (optional)
     *
     * @return Result
     */
    public function readPostResult($relativeUrl, $entity, array $getParameters = null)
    {
        $uri = $this->uri->withPath($this->http_baseurl . $relativeUrl);
        if (null !== $getParameters) {
            $uri = $uri->withQuery(http_build_query($getParameters));
        }
        $request = self::createRequest('POST', $uri);
        $body = new Psr7\Stream();
        $body->write(json_encode($entity));
        $request = $request->withBody($body);

        return $this->readResult($request);
    }

    /**
     * Creates a DELETE-Http-Request and fetches the response
     *
     * @param String $relativeUrl
     * @param Array $getParameters (optional)
     *
     * @return Result
     */
    public function readDeleteResult($relativeUrl, array $getParameters = null)
    {
        $uri = $this->uri->withPath($this->http_baseurl . $relativeUrl);
        if (null !== $getParameters) {
            $uri = $uri->withQuery(http_build_query($getParameters));
        }
        $request = self::createRequest('DELETE', $uri);        
        return $this->readResult($request);
    }

    protected function readResult(
        \Psr\Http\Message\RequestInterface $request = null,
        $try = 0
    ) {
        $response = $this->readResponse($request);
        $result = new Result($response, $request);
        if ($response->getStatuscode() == 500) {
            try {
                $result->getData();
            } catch (Exception $exception) {
                if ($try < 3 && in_array($exception->template, [
                    "BO\\Zmsdb\\Exception\\Pdo\\DeadLockFound",
                    "BO\\Zmsdb\\Exception\\Pdo\\LockTimeout",
                ])) {
                    usleep(rand(1000000, 3000000));
                    return $this->readResult($request, $try + 1);
                }
            }
        }
        return $result;
    }

    public static function createRequest(string $method, UriInterface $uri): RequestInterface
    {
        $request = new Psr7\Request($method, $uri, 'php://memory', new Headers([], []));
        return $request;
    }
}
