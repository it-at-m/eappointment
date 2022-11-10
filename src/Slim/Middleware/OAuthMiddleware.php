<?php
namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \BO\Slim\Middleware\OAuth\KeycloakAuth;

class OAuthMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        $methodName = 'handle'. \App::OIDC_AUTHORIZATION_TYPE . 'Instance';
        $response = $this->$methodName($request, $response);
        return $next($request, $response);
    }

    protected function handleKeycloakInstance(ServerRequestInterface $request, ResponseInterface $response)
    {
        $instance = new KeycloakAuth();
        if ('logout/' === $request->getUri()->getPath()) {
            return $instance->doLogout($response);
        } elseif ('oidc/' === $request->getUri()->getPath()) {
            if (! $request->getParam("code") && '' == \BO\Zmsclient\Auth::getKey()) {
                $authUrl = $instance->getProvider()->getAuthorizationUrl();
                \BO\Zmsclient\Auth::setKey($instance->getProvider()->getState());
                return $response->withRedirect($authUrl, 301);
            } elseif ($request->getParam("state") !== \BO\Zmsclient\Auth::getKey()) {
                \BO\Zmsclient\Auth::removeKey();
            }
            return $instance->doLogin($request, $response);
        } elseif ('workstation/status/' !== $request->getUri()->getPath() && '/' !== $request->getUri()->getPath()) {
            error_log($request->getUri()->getPath());
            if (! $instance->writeNewAccessTokenIfExpired($response)) {
                return $instance->doLogout($response);
            }
        }
        return $response;
    }

    protected function handleGitlabInstance(ServerRequestInterface $request, ResponseInterface $response)
    {
        return $response;
    }
}
