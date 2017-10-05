<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsadmin\Helper\LoginForm;
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
        $form = LoginForm::fromLoginParameters();
        $validate = Validator::param('login_form_validate')->isBool()->getValue();
        $loginData = ($validate) ? $form->getStatus() : null;
        if ($loginData && !$form->hasFailed()) {
            $userAccount = new \BO\Zmsentities\Useraccount(array(
                'id' => $loginData['loginName']['value'],
                'password' => $loginData['password']['value'],
                'departments' => array('id' => 0) // required in schema validation
            ));
            try {
                $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
            } catch (\BO\Zmsclient\Exception $exception) {
                if ($exception->template == 'BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn') {
                    \BO\Zmsclient\Auth::setKey($exception->data['authkey']);
                    throw $exception;
                } elseif ($exception->template == 'BO\Zmsapi\Exception\Useraccount\AuthKeyFound') {
                    throw $exception;
                }
            }
            if (array_key_exists('authkey', $workstation)) {
                \BO\Zmsclient\Auth::setKey($workstation->authkey);
                return \BO\Slim\Render::redirect('workstationSelect', array(), array());
            }
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung',
                'loginData' => $loginData
            )
        );
    }
}
