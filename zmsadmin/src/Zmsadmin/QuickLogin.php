<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Workstation as Entity;

class QuickLogin extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @param \Psr\Http\Message\RequestInterface|\BO\Slim\Request $request
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $loginData = Helper\LoginForm::fromQuickLogin();
        if ($loginData->hasFailed()) {
            throw new \BO\Zmsentities\Exception\QuickLoginFailed();
        }
        $loginData = $loginData->getStatus();

        // Check for required fields before proceeding
        if (!isset($loginData['loginName']['value']) || !isset($loginData['password']['value'])) {
            throw new \BO\Zmsentities\Exception\QuickLoginFailed();
        }

        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => $loginData['loginName']['value'],
            'password' => $loginData['password']['value']
        ));

        try {
            $workstation = \App::$http
                ->readPostResult('/workstation/login/', $userAccount)->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            //ignore double login exception on quick login
            if ($exception->template == 'BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn') {
                $workstation = new Entity($exception->data);
            } else {
                throw new \BO\Zmsentities\Exception\QuickLoginFailed();
            }
        }

        if (!isset($workstation)) {
            throw new \BO\Zmsentities\Exception\QuickLoginFailed();
        }

        \BO\Zmsclient\Auth::setKey($workstation->authkey, time() + \App::SESSION_DURATION);
        $workstation->scope = new \BO\Zmsentities\Scope(array('id' => $loginData['scope']['value']));
        $workstation->hint = $loginData['hint']['value'];
        $workstation->name = $loginData['workstation']['value'];
        $workstation = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
        $basePath = $request->getBasePath();

        return $response->withRedirect($basePath . '/' . trim($loginData['redirectUrl']['value'], "/"));
    }
}
