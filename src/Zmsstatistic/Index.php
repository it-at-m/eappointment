<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use \BO\Zmsstatistic\Helper\LoginForm;
use \BO\Mellon\Validator;

class Index extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }
        $form = LoginForm::fromLoginParameters();
        $validate = Validator::param('login_form_validate')->isBool()->getValue();
        $loginData = ($validate) ? $form->getStatus() : null;
        if ($loginData && !$form->hasFailed()) {
            return $this->testLogin($loginData, $response);
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung',
                'workstation' => $workstation,
                'loginData' => $loginData
            )
        );
    }

    protected function testLogin($loginData, $response)
    {
        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => $loginData['loginName']['value'],
            'password' => $loginData['password']['value']
        ));
        try {
            $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
            if (array_key_exists('authkey', $workstation)) {
                \BO\Zmsclient\Auth::setKey($workstation->authkey);
                return \BO\Slim\Render::redirect('workstationSelect', array(), array());
            }
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template == 'BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn') {
                \BO\Zmsclient\Auth::setKey($exception->data['authkey']);
                throw $exception;
            } elseif ($exception->template == 'BO\Zmsapi\Exception\Useraccount\AuthKeyFound') {
                throw $exception;
            }
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung gescheitert',
                'loginfailed' => true,
                'workstation' => null,
                'loginData' => $loginData
            )
        );
    }
}
