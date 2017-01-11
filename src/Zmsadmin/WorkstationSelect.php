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
class WorkstationSelect extends BaseController
{
    /**
     * @return String
     */

    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }
        $workstation->hasDepartmentList();
        $form = LoginForm::fromAdditionalParameters();
        $validate = Validator::param('workstation_select_form_validate')->isBool()->getValue();
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
            'page/workstationSelect.twig',
            array(
                'title' => 'Standort und Arbeitsplatz auswählen',
                'advancedData' => $advancedData,
                'workstation' => $workstation
            )
        );
    }
}
