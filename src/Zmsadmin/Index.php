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
                'password' => $form['password']->getValue()
            ));
            try {
                $workstation = \App::$http->readPostResult(
                    '/workstation/'. $userAccount->id .'/',
                    $userAccount
                )->getEntity();
                Auth::setKey($workstation->authKey);

                $workstation->name = $form['workstation']->getValue();
                $workstation->scope['id'] = $form['scope']->getValue();
                $userAccount->addDepartment($form['department']->getValue());
                $workstation->useraccount = $userAccount;
                $workstation = \App::$http->readPostResult('/workstation/', $workstation)->getEntity();
                $redirectTo = ($workstation->name == 0) ? 'counter' : 'workstation';
            } catch (\Exception $exception) {
                return Helper\Render::error($request, $exception);
            }
            return Helper\Render::checkedRedirect(
                self::$errorHandler,
                $redirectTo,
                array(),
                array()
            );
        }

        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences'=>1))->getCollection();
        $organisationList = $ownerList->getOrganisationsByOwnerId(23);
        $validate = Validator::param('form_validate')->isBool()->getValue();
        $loginData = ($validate) ? $form->getStatus() : null;
        self::$errorHandler->error = ($loginData) ? 'login_failed' : '';
        return Helper\Render::checkedHtml(
            self::$errorHandler,
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung',
                'loginData' => $loginData,
                'organisationList' => $organisationList->sortByName()
            )
        );
    }
}
