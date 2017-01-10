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
        $form = LoginForm::fromLoginParameters();
        $validate = Validator::param('login_form_validate')->isBool()->getValue();
        $departmentId = Validator::param('department')->isNumber()->getValue();
        $loginData = ($validate) ? $form->getStatus() : null;
        if ($loginData && !$form->hasFailed() && LoginForm::setLoginAuthKey($form)) {
            return \BO\Slim\Render::redirect(
                'indexAdvancedLogin',
                array('departmentId' => $departmentId),
                array()
            );
        }

        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences'=>3))->getCollection();
        $organisationList = $ownerList->getOrganisationsByOwnerId(23);

        return \BO\Slim\Render::withHtml(
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
