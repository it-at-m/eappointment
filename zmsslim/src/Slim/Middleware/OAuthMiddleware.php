<?php
namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Slim\Factory\ResponseFactory;

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
        RequestHandlerInterface  $next
    ) {
        $response = (new ResponseFactory())->createResponse(200, '');
        $request = $request->withAttribute('authentificationHandler', $this->authentificationHandler);
        $queryParams = $request->getQueryParams();
        $oidcProviderName = isset($queryParams['provider'])
            ? $queryParams['provider'] : \BO\Zmsclient\Auth::getOidcProvider();
        if ($oidcProviderName) {
            $oidcInstance = static::$authInstances[$oidcProviderName];
            $instance = new $oidcInstance();
            $response = $this->{$this->handlerCall}($request, $response, $instance, $next);
        }
        return $response;
    }

    private function handleLogin(ServerRequestInterface $request, ResponseInterface $response, $instance, $next)
    {
        if (! $request->getParam("code") && '' == \BO\Zmsclient\Auth::getKey()) {
            // Log initial OAuth request
            \App::$log->info('OAuth login initiated', [
                'provider' => $request->getQueryParams()['provider'] ?? \BO\Zmsclient\Auth::getOidcProvider(),
                'event' => 'oauth_login_start'
            ]);
            return $response->withRedirect($this->getAuthUrl($request, $instance), 301);
        } elseif ($request->getParam("state") !== \BO\Zmsclient\Auth::getKey()) {
            // Log invalid state parameter
            \App::$log->warning('OAuth state mismatch', [
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'event' => 'oauth_state_mismatch'
            ]);
            \BO\Zmsclient\Auth::removeKey();
            \BO\Zmsclient\Auth::removeOidcProvider();
            return $response->withRedirect($this->getAuthUrl($request, $instance), 301);
        }
    
        if ('login' == $request->getAttribute('authentificationHandler')) {
            try {
                // Attempt login
                $instance->doLogin($request, $response);
                
                // Log successful login with username
                $resourceOwner = $instance->getProvider()->getResourceOwner($instance->getAccessToken());
                \App::$log->info('OAuth login successful', [
                    'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                    'username' => $resourceOwner->getUsername(),
                    'event' => 'oauth_login_success'
                ]);
                
                $response = $next->handle($request);
                return $response;
            } catch (\Exception $e) {
                // Log login failures with details
                \App::$log->error('OAuth login failed', [
                    'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                    'error' => $e->getMessage(),
                    'error_code' => $e->getCode(),
                    'event' => 'oauth_login_error'
                ]);
                throw $e;
            }
        }
        return $response;
    }

    private function handleLogout(ServerRequestInterface $request, ResponseInterface $response, $instance)
    {
        if ('logout' == $request->getAttribute('authentificationHandler') && ! $request->getParam('state')) {
            try {
                // Log logout event
                $resourceOwner = $instance->getProvider()->getResourceOwner($instance->getAccessToken());
                \App::$log->info('OAuth logout', [
                    'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                    'username' => $resourceOwner->getUsername(),
                    'event' => 'oauth_logout'
                ]);
                return $instance->doLogout($response);
            } catch (\Exception $e) {
                \App::$log->error('OAuth logout failed', [
                    'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                    'error' => $e->getMessage(),
                    'event' => 'oauth_logout_error'
                ]);
                throw $e;
            }
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

    private function getAuthUrl(ServerRequestInterface $request, $instance)
    {
        $authUrl = $instance->getProvider()->getAuthorizationUrl();
        \BO\Zmsclient\Auth::setOidcProvider($request->getParam('provider'));
        \BO\Zmsclient\Auth::setKey($instance->getProvider()->getState(), time() + \App::SESSION_DURATION);
        return $authUrl;
    }
}
