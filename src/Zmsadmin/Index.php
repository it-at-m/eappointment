<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsadmin\Helper\LoginForm;
use \BO\Mellon\Validator;

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
        $validate = Validator::param('form_validate')->isBool()->getValue();
        $loginData = ($validate) ? $form->getStatus() : null;

        if ($loginData && !$form->hasFailed()) {
            $loginRedirect = LoginForm::setLoginRedirect($form);
            return Helper\Render::checkedRedirect(
                self::$errorHandler,
                $loginRedirect,
                array(),
                array()
            );
        }

        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences'=>1))->getCollection();
        $organisationList = $ownerList->getOrganisationsByOwnerId(23);

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
