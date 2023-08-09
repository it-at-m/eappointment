<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsentities\Workstation as Entity;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class QuickLogin extends BaseController
{
    protected $withAccess = false;

    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */

    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $loginData = Helper\LoginForm::fromQuickLogin();
        if ($loginData->hasFailed()) {
            throw new \BO\Zmsentities\Exception\QuickLoginFailed();
        }
        $loginData = $loginData->getStatus();
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
            }
        }

        \BO\Zmsclient\Auth::setKey($workstation->authkey, \App::SESSION_DURATION);
        $workstation->scope = new \BO\Zmsentities\Scope(array('id' => $loginData['scope']['value']));
        $workstation->hint = $loginData['hint']['value'];
        $workstation->name = $loginData['workstation']['value'];
        \App::$http->readPostResult('/workstation/', $workstation)->getEntity();

        $basePath = $request->getBasePath();
        return $response->withRedirect($basePath .'/'. trim($loginData['redirectUrl']['value'], "/"));
    }
}
