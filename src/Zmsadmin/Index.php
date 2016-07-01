<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsadmin\Helper\LoginForm;
use \BO\Mellon\Validator;
use \BO\Zmsclient\Auth;

/**
  * Handle requests concerning services
  *
  */
class Index extends BaseController
{
    /**
     * @return String
     */

    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $form = LoginForm::fromParameters();
        if ($form->hasFailed()) {
            $validate = Validator::param('form_validate')->isBool()->getValue();
            $loginData = ($validate) ? $form->getStatus() : null;
            self::$errorHandler->error = 'login_failed';
            return Helper\Render::checkedHtml(
                self::$errorHandler,
                $response,
                'page/index.twig',
                array(
                    'title' => 'Anmeldung',
                    'loginData' => $loginData
                )
            );
        }

        $form = $form->getValues();
        $userAccount = new \BO\Zmsentities\UserAccount(array(
            'id' => $form['loginName']->getValue(),
            'password' => md5($form['password']->getValue())
        ));
        $workstation = \App::$http->readPostResult(
            '/workstation/'. $userAccount->id .'/',
            $userAccount
        )->getEntity();

        Auth::setKey($workstation->authKey, \App::IDENTIFIER);

        return Helper\Render::checkedRedirect(
            self::$errorHandler,
            'workstation',
            array(),
            array()
        );
    }
}
