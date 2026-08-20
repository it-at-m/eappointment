<?php

namespace BO\Slim\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use BO\Slim\Factory\ResponseFactory;
use BO\Slim\Middleware\OAuth\KeycloakInstance;
use BO\Zmsclient\Auth;
use Slim\Psr7\Factory\StreamFactory;

/**
 * @SuppressWarnings(PHPMD)
 */

class OAuthMiddleware
{
    /**
     * List of authentification types to init specific instance
     *
     * @var array<string, class-string<KeycloakInstance>>
     */
    public static array $authInstances = [
        'keycloak' => '\BO\Slim\Middleware\OAuth\KeycloakInstance'
    ];

    /**
     * List of request pathes with assigned handler in oidc instance
     *
     * @var array<string, string>
     */
    protected array $handlerList = [
        'login' => 'handleLogin',
        'logout' => 'handleLogout',
        'refresh' => 'handleRefreshToken'
    ];

    protected string $authentificationHandler = '';

    public function __construct(string $handler = 'login')
    {
        $this->authentificationHandler = isset($this->handlerList[$handler]) ? $handler : 'login';
    }

    /**
     * Set the authorizsationType attribute to request and init authorization method
     *
     * @param ServerRequestInterface $request PSR7 request
     * @param RequestHandlerInterface $next Next middleware
     *
     * @return ResponseInterface
     */
    public function __invoke(
        ServerRequestInterface $request,
        RequestHandlerInterface $next
    ): ResponseInterface {
        $response = (new ResponseFactory())->createResponse(200, '');
        $request = $request->withAttribute('authentificationHandler', $this->authentificationHandler);
        $queryParams = $request->getQueryParams();
        $providerFromQuery = $queryParams['provider'] ?? null;
        $oidcProviderName = is_string($providerFromQuery) && $providerFromQuery !== ''
            ? $providerFromQuery
            : Auth::getOidcProvider();

        if (
            is_string($oidcProviderName)
            && $oidcProviderName !== ''
            && isset(static::$authInstances[$oidcProviderName])
        ) {
            $oidcInstance = static::$authInstances[$oidcProviderName];
            /** @psalm-suppress UnsafeInstantiation */
            $instance = new $oidcInstance();
            $response = match ($this->authentificationHandler) {
                'logout' => $this->handleLogout($request, $response, $instance),
                'refresh' => $this->handleRefreshToken($request, $response, $instance),
                default => $this->handleLogin($request, $response, $instance, $next),
            };
        } else {
            \App::$log->error('Unknown OIDC provider requested', [
                'event' => 'oauth_unknown_provider',
                'provider' => is_string($oidcProviderName) && $oidcProviderName !== '' ? $oidcProviderName : 'none',
                'available_providers' => array_keys(static::$authInstances),
                'handler' => $this->authentificationHandler,
                'timestamp' => date('c'),
                'request_uri' => $request->getUri()->getPath(),
                'session_id' => session_id()
            ]);
            $stream = (new StreamFactory())->createStream();
            $payload = json_encode(['error' => 'Unknown OIDC provider']);
            $stream->write($payload !== false ? $payload : '{"error":"Unknown OIDC provider"}');
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                ->withBody($stream);
        }
        return $response;
    }

    private function handleLogin(
        ServerRequestInterface $request,
        ResponseInterface $response,
        KeycloakInstance $instance,
        RequestHandlerInterface $next
    ): ResponseInterface {
        $query = $request->getQueryParams();
        $code = $query['code'] ?? null;
        $state = $query['state'] ?? null;
        $authKey = Auth::getKey();
        if (($code === null || $code === '') && ($authKey === null || $authKey === '')) {
            return $this->withRedirect($response, $this->getAuthUrl($request, $instance), 301);
        } elseif ($state !== $authKey) {
            Auth::removeKey();
            Auth::removeOidcProvider();
            return $this->withRedirect($response, $this->getAuthUrl($request, $instance), 301);
        }
        if ('login' == $request->getAttribute('authentificationHandler')) {
            $instance->doLogin($request);
            $response = $next->handle($request);
            return $response;
        }
        return $response;
    }

    private function handleLogout(
        ServerRequestInterface $request,
        ResponseInterface $response,
        KeycloakInstance $instance
    ): ResponseInterface {
        $state = $request->getQueryParams()['state'] ?? null;
        if (
            'logout' == $request->getAttribute('authentificationHandler') &&
            ($state === null || $state === '')
        ) {
            return $instance->doLogout($response);
        }
        return $response;
    }

    private function handleRefreshToken(
        ServerRequestInterface $request,
        ResponseInterface $response,
        KeycloakInstance $instance
    ): ResponseInterface {
        if (
            'refresh' == $request->getAttribute('authentificationHandler') &&
            ! $instance->writeNewAccessTokenIfExpired()
        ) {
            return $instance->doLogout($response);
        }
        return $response;
    }

    private function getAuthUrl(ServerRequestInterface $request, KeycloakInstance $instance): string
    {
        $authUrl = $instance->getProvider()->getAuthorizationUrl();
        $provider = $request->getQueryParams()['provider'] ?? null;
        if (is_string($provider) && $provider !== '') {
            Auth::setOidcProvider($provider);
        }
        Auth::setKey($instance->getProvider()->getState(), time() + \App::SESSION_DURATION);
        return $authUrl;
    }

    private function withRedirect(ResponseInterface $response, string $url, int $status): ResponseInterface
    {
        return $response->withHeader('Location', $url)->withStatus($status);
    }
}
