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
class IndexAdvancedLogin extends BaseController
{
    /**
     * @return String
     */

    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $departmentId = Validator::value($args['departmentId'])->isNumber()->getValue();
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }
        $form = LoginForm::fromAdditionalParameters();
        $validate = Validator::param('login_advanced_form_validate')->isBool()->getValue();
        $advancedData = ($validate) ? $form->getStatus() : null;

        if ($advancedData && !$form->hasFailed()) {
            $loginRedirect = LoginForm::setLoginRedirect($form, $workstation);
            return \BO\Slim\Render::redirect(
                $loginRedirect,
                array(),
                array()
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung - Standort und Platzauswahl',
                'loginAdvanced' => 1,
                'advancedData' => $advancedData,
                'scopeList' =>  \App::$http->readGetResult('/scope/department/'. $departmentId .'/')->getCollection()
            )
        );
    }
}
