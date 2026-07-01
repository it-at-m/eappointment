<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsclient\ModuleAccess;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Index extends BaseController
{
    protected $withAccess = false;

    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }

        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();
        $input = $request->getParsedBody();
        $oidclogin = $request->getAttribute('validator')->getParameter('oidclogin')->isString()->getValue();
        if ($request->getMethod() === 'POST') {
            $loginData = $this->testLogin($input);
            if ($loginData instanceof Workstation && $loginData->offsetExists('authkey')) {
                \BO\Zmsclient\Auth::setKey($loginData->authkey);
                return ModuleAccess::rejectWrongModuleAccess(ModuleAccess::MODULE_STATISTIC, $loginData, $response)
                    ?? \BO\Slim\Render::redirect('workstationSelect', array(), array());
            }

            return Render::withHtml(
                $response,
                'page/index.twig',
                array(
                    'title' => 'Anmeldung gescheitert',
                    'loginfailed' => true,
                    'workstation' => null,
                    'exception' => $loginData,
                    'oidcproviderlist' => $this->getProviderList($config),
                    'oidclogin' => $oidclogin,
                    'showloginform' => (! $oidclogin)
                )
            );
        } else {
            if ($workstation instanceof Workstation && $workstation->hasId()) {
                if ($wrongModuleResponse = ModuleAccess::rejectWrongModuleAccess(ModuleAccess::MODULE_STATISTIC, $workstation, $response)) {
                    return $wrongModuleResponse;
                }
            }
            return Render::withHtml(
                $response,
                'page/index.twig',
                array(
                    'title' => 'Anmeldung',
                    'config' => $config,
                    'workstation' => $workstation,
                    'oidcproviderlist' => $this->getProviderList($config),
                    'oidclogin' => $oidclogin,
                    'showloginform' => (! $oidclogin)
                )
            );
        }
    }

    #[\Override]
    protected function testLogin($input)
    {
        $userAccount = new Useraccount(array(
            'id' => $input['loginName'],
            'password' => $input['password'],
            'departments' => array('id' => 0) // required in schema validation
        ));
        try {
            $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();

            $sessionHash = hash('sha256', $workstation->authkey);
            \App::$log->info('Login successful', [
                'event' => 'auth_login_success',
                'timestamp' => date('c'),
                'username' => $userAccount->id,
                'hashed_session_token' => $sessionHash,
                'application' => 'zmsstatistic'
            ]);

            return $workstation;
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = Helper\TwigExceptionHandler::getExceptionTemplate($exception);
            if ('BO\Zmsentities\Exception\SchemaValidation' == $exception->template) {
                $exceptionData = [
                  'template' => 'exception/bo/zmsbackend/useraccount/exception/invalidcredentials.twig'
                ];
                $exceptionData['data']['password']['messages'] = [
                    'Der Nutzername oder das Passwort wurden falsch eingegeben'
                ];
                \App::$log->info('Login failed - invalid credentials', [
                    'event' => 'auth_login_failed',
                    'timestamp' => date('c'),
                    'username' => $userAccount->id,
                    'error_type' => 'invalid_credentials',
                    'application' => 'zmsstatistic'
                ]);
            } elseif ('BO\Zmsbackend\Useraccount\Exception\UserAlreadyLoggedIn' == $exception->template) {
                \BO\Zmsclient\Auth::setKey($exception->data['authkey'], time() + \App::SESSION_DURATION);
                \App::$log->info('User already logged in - reusing existing session', [
                    'event' => 'auth_session_reuse',
                    'timestamp' => date('c'),
                    'username' => $userAccount->id,
                    'hashed_session_token' => hash('sha256', $exception->data['authkey']),
                    'application' => 'zmsstatistic'
                ]);
                throw $exception;
            } elseif (
                '' != $exception->template
                && \App::$slim->getContainer()->get('view')->getLoader()->exists($template)
            ) {
                $exceptionData = [
                  'template' => $template,
                  'data' => $exception->data
                ];
                \App::$log->info('Login failed - other error', [
                    'event' => 'auth_login_failed',
                    'timestamp' => date('c'),
                    'username' => $userAccount->id,
                    'error_type' => 'other',
                    'error_message' => $exception->getMessage(),
                    'application' => 'zmsstatistic'
                ]);
            } else {
                throw $exception;
            }
        }
        return $exceptionData;
    }
    protected function getProviderList($config)
    {
        $allowedProviderList = explode(',', $config->getPreference('oidc', 'provider') ?? '');
        $oidcproviderlist = [];
        foreach (\BO\Slim\Middleware\OAuthMiddleware::$authInstances as $provider => $authInstance) {
            if (
                0 < count($allowedProviderList) &&
                class_exists($authInstance) &&
                in_array($provider, $allowedProviderList)
            ) {
                $oidcproviderlist[] = $provider;
            }
        }
        return $oidcproviderlist;
    }
}
