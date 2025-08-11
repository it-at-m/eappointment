<?php

namespace BO\Slim\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Slim\Factory\ResponseFactory;
use BO\Zmsclient\Psr7\Stream;

/**
 * @SuppressWarnings(PHPMD)
 */

class OAuthMiddleware
{
    /**
     * List of authentification types to init specific instance
     *
     * @var array
     */
    public static $authInstances = [
        'keycloak' => '\BO\Slim\Middleware\OAuth\KeycloakInstance'
    ];

    /**
     * List of request pathes with assigned handler in oidc instance
     *
     * @var array
     */
    protected $handlerList = [
        'login' => 'handleLogin',
        'logout' => 'handleLogout',
        'refresh' => 'handleRefreshToken'
    ];

    protected $handlerCall = '';

    protected $authentificationHandler = '';

    public function __construct($handler = 'login')
    {
        $this->authentificationHandler = $handler;
        $this->handlerCall = $this->handlerList[$handler];
    }

    /**
     * Set the authorizsationType attribute to request and init authorization method
     *
     * @param ServerRequestInterface $request PSR7 request
     * @param ResponseInterface $response     PSR7 response
     * @param callable $next                  Next middleware
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $next
    ) {
        $response = (new ResponseFactory())->createResponse(200, '');
        $request = $request->withAttribute('authentificationHandler', $this->authentificationHandler);
        $queryParams = $request->getQueryParams();
        $oidcProviderName = isset($queryParams['provider'])
            ? $queryParams['provider'] : \BO\Zmsclient\Auth::getOidcProvider();

        if ($oidcProviderName && isset(static::$authInstances[$oidcProviderName])) {
            $oidcInstance = static::$authInstances[$oidcProviderName];
            $instance = new $oidcInstance();
            $response = $this->{$this->handlerCall}($request, $response, $instance, $next);
        } else {
            \App::$log->error('Unknown OIDC provider requested', [
                'event' => 'oauth_unknown_provider',
                'provider' => $oidcProviderName ?: 'none',
                'available_providers' => array_keys(static::$authInstances),
                'handler' => $this->authentificationHandler,
                'timestamp' => date('c'),
                'request_uri' => $request->getUri()->getPath(),
                'session_id' => session_id()
            ]);
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                ->withBody(Stream::create(json_encode(['error' => 'Unknown OIDC provider'])));
        }
        return $response;
    }

    private function handleLogin(ServerRequestInterface $request, ResponseInterface $response, $instance, $next)
    {
        if (! $request->getParam("code") && '' == \BO\Zmsclient\Auth::getKey()) {
            return $response->withRedirect($this->getAuthUrl($request, $instance), 301);
        } elseif ($request->getParam("state") !== \BO\Zmsclient\Auth::getKey()) {
            \BO\Zmsclient\Auth::removeKey();
            \BO\Zmsclient\Auth::removeOidcProvider();
            return $response->withRedirect($this->getAuthUrl($request, $instance), 301);
        }
        if ('login' == $request->getAttribute('authentificationHandler')) {
            $instance->doLogin($request, $response);
            $response = $next->handle($request);
            return $response;
        }
        return $response;
    }

    private function handleLogout(ServerRequestInterface $request, ResponseInterface $response, $instance)
    {
        if (
            'logout' == $request->getAttribute('authentificationHandler') &&
            ! $request->getParam('state')
        ) {
            return $instance->doLogout($response);
        }
        return $response;
    }

    private function handleRefreshToken(ServerRequestInterface $request, ResponseInterface $response, $instance)
    {
        if (
            'refresh' == $request->getAttribute('authentificationHandler') &&
            ! $instance->writeNewAccessTokenIfExpired()
        ) {
            return $instance->doLogout($response);
        }
        return $response;
    }

    private function getAuthUrl(ServerRequestInterface $request, $instance)
    {
        $authUrl = $instance->getProvider()->getAuthorizationUrl();
        \BO\Zmsclient\Auth::setOidcProvider($request->getParam('provider'));
        \BO\Zmsclient\Auth::setKey($instance->getProvider()->getState(), time() + \App::SESSION_DURATION);
        return $authUrl;
    }
}
