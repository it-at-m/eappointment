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
        if (!$form->hasFailed()) {
            $form = $form->getValues();
            $userAccount = new \BO\Zmsentities\UserAccount(array(
                'id' => $form['loginName']->getValue(),
                'password' => md5($form['password']->getValue())
            ));

            try {
                $workstation = \App::$http->readPostResult(
                    '/workstation/'. $userAccount->id .'/',
                    $userAccount
                )->getEntity();
                Auth::setKey($workstation->authKey);
            } catch (\Exception $exception) {
                return Render::error($response, $exception);
            }
            return Helper\Render::checkedRedirect(
                self::$errorHandler,
                'workstation',
                array(),
                array()
            );
        }

        $validate = Validator::param('form_validate')->isBool()->getValue();
        $loginData = ($validate) ? $form->getStatus() : null;
        self::$errorHandler->error = ($loginData) ? 'login_failed' : '';
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
}
