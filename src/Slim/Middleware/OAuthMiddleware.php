<?php
namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;

class OAuthMiddleware
{
    /**
     * List of authentification types to init specific instance
     *
     * @var array
     */
    public static $authInstances = [
        'keycloak' => '\BO\Slim\Middleware\OAuth\KeycloakInstance',
        'gitlab' => '\BO\Slim\Middleware\OAuth\GitlabInstance'
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
        ResponseInterface $response,
        callable $next
    ) {
        $request = $request->withAttribute('authentificationHandler', $this->authentificationHandler);
        $oidcProviderName = ($request->getParam('provider')) ?
            $request->getParam('provider') :
            \BO\Zmsclient\Auth::getOidcProvider();
        if ($oidcProviderName) {
            $oidcInstance = static::$authInstances[$oidcProviderName];
            $instance = new $oidcInstance();
            $response = $this->{$this->handlerCall}($request, $response, $instance);
        }
        return $next($request, $response);
    }

    private function handleLogin(ServerRequestInterface $request, ResponseInterface $response, $instance)
    {
        if (! $request->getParam("code") && '' == \BO\Zmsclient\Auth::getKey()) {
            $authUrl = $instance->getProvider()->getAuthorizationUrl();
            \BO\Zmsclient\Auth::setOidcProvider($request->getParam('provider'));
            \BO\Zmsclient\Auth::setKey($instance->getProvider()->getState());
            return $response->withRedirect($authUrl, 301);
        } elseif ($request->getParam("state") !== \BO\Zmsclient\Auth::getKey()) {
            \BO\Zmsclient\Auth::removeKey();
            \BO\Zmsclient\Auth::removeOidcProvider();
            throw new \BO\Slim\Exception\OAuthInvalid();
        }
        if ('login' == $request->getAttribute('authentificationHandler')) {
            return $instance->doLogin($request, $response);
        }
        return $response;
    }

    private function handleLogout(ServerRequestInterface $request, ResponseInterface $response, $instance)
    {
        if ('logout' == $request->getAttribute('authentificationHandler') &&
            ! $request->getParam('state')
        ) {
            return $instance->doLogout($response);
        }
        return $response;
    }

    private function handleRefreshToken(ServerRequestInterface $request, ResponseInterface $response, $instance)
    {
        if ('refresh' == $request->getAttribute('authentificationHandler') &&
            ! $instance->writeNewAccessTokenIfExpired()
        ) {
            return $instance->doLogout($response);
        }
        return $response;
    }
}
