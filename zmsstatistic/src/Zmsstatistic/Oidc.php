<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsclient\ModuleAccess;
use BO\Zmsclient\OidcHandler;

class Oidc extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            $state = $request->getParam('state');
            $handler = new OidcHandler(\App::$http);
            $result = $handler->handleCallback($state, 'zmsstatistic');

            if ($wrongModuleResponse = ModuleAccess::rejectWrongModuleAccess(ModuleAccess::MODULE_STATISTIC, $result['workstation'], $response)) {
                return $wrongModuleResponse;
            }

            if ($result['redirect_to_index']) {
                return \BO\Slim\Render::redirect(
                    'index',
                    [],
                    [
                        'oidclogin' => true
                    ]
                );
            }

            return \BO\Slim\Render::redirect(
                'workstationSelect',
                [],
                []
            );
        } catch (\BO\Slim\Exception\OAuthInvalid $e) {
            throw $e;
        } catch (\Exception $e) {
            \App::$log->error('OIDC Login error', [
                'event' => 'oauth_login_error',
                'timestamp' => date('c'),
                'provider' => \BO\Zmsclient\Auth::getOidcProvider(),
                'application' => 'zmsstatistic',
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }
}
