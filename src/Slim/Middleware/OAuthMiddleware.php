<?php
namespace BO\Slim\Middleware;

use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \BO\Slim\Middleware\OAuth\OAuth;

class OAuthMiddleware
{
    public function __invoke(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next
    ) {
        if(\App::ZMS_AUTHORIZATION_TYPE === "Keycloak"){
            if ($this->checkUserLoggedIn() || $this->loginUser($request)) {
                return $next($request, $response);
            } else {
                $exceptionData = [
                    'template' => 'exception/bo/zmsapi/exception/useraccount/keycloakAuthError.twig'
                ];
                return \BO\Slim\Render::withHtml(
                    $response,
                    'page/index.twig',
                    array(
                        'title' => 'Anmeldung gescheitert',
                        'loginfailed' => true,
                        'workstation' => null,
                        'exception' => $exceptionData,
                        'showloginform' => false,
                    )
                );
            }
        }

        return $next($request, $response);
    }

    private function checkUserLoggedIn(){
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
            if ($workstation->getUseraccount()->hasId()) {
                return true;
            }
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }

        return false;
    }

    private function loginUser($request){
        $oauth = new OAuth();
        $oauth->setAccessTokenPayload($request->getParam("code"), $request->getParam("state"));

        if($oauth->testAccessRight()){
            \BO\Zmsclient\Auth::setKey($request->getParam("code"));
            $workstation = \App::$http->readPostResult('/workstation/oauth/', $oauth->getUserData(), ['X-Authkey' => \BO\Zmsclient\Auth::getKey()] )->getEntity();

            if ($workstation->offsetExists('authkey')) {
                \BO\Zmsclient\Auth::setKey($workstation->authkey);
                return true;
            }
        }

        return false;
    }
}
