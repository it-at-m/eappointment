<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Mellon\Validator;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Exception\QuickLoginFailed;
use BO\Zmsclient\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class QuickLogin extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $loginData = Helper\LoginForm::fromQuickLogin();
        if ($loginData->hasFailed()) {
            throw new QuickLoginFailed();
        }
        $loginData = $loginData->getStatus();
        $userAccount = new Useraccount(array(
            'id' => $loginData['loginName']['value'],
            'password' => $loginData['password']['value']
        ));

        try {
            $workstation = \App::$http
                ->readPostResult('/workstation/login/', $userAccount)->getEntity();
        } catch (Exception $exception) {
            //ignore double login exception on quick login
            if ($exception->template == 'BO\Zmsbackend\Useraccount\Exception\UserAlreadyLoggedIn') {
                $workstation = new Workstation($exception->data);
            }
        }

        \BO\Zmsclient\Auth::setKey($workstation->authkey, time() + \App::SESSION_DURATION);
        $workstation->scope = new Scope(array('id' => $loginData['scope']['value']));
        $workstation->hint = $loginData['hint']['value'];
        $workstation->name = $loginData['workstation']['value'];
        \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
        $basePath = $request->getBasePath();

        return $response->withRedirect($basePath . '/' . trim($loginData['redirectUrl']['value'], "/"));
    }
}
