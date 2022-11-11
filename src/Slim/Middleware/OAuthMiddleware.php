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
        if ("Keycloak" === \App::OIDC_AUTHORIZATION_TYPE){
            $response = $this->handleKeycloakInstance($request, $response);
        }
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
            if (! $instance->writeNewAccessTokenIfExpired()) {
                return $instance->doLogout($response);
            }
        }
        return $response;
    }
}
