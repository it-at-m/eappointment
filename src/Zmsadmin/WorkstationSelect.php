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
     * @SuppressWarnings(Parameter)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3])->getEntity();
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect('index', array('error' => 'login_failed'));
        }
        $workstation->hasDepartmentList();

        $input = $request->getParsedBody();
        if (is_array($input) && (array_key_exists('scope', $input))) {
            $form = LoginForm::fromAdditionalParameters();
            $formData = $form->getStatus();
            $selectedDate = Validator::param('selectedDate')->isString()->getValue();
            $queryParams = ($selectedDate) ? array('date' => $selectedDate) : array();
            $isUpdated = LoginForm::writeWorkstationUpdate($form, $workstation);
            if (! $form->hasFailed() && $isUpdated) {
                return \BO\Slim\Render::redirect(
                    $workstation->getRedirect(),
                    array(),
                    $queryParams
                );
            }
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstationSelect.twig',
            array(
                'title' => 'Standort und Arbeitsplatz auswählen',
                'advancedData' => $formData,
                'workstation' => $workstation,
                'menuActive' => 'select',
                'today' => \App::$now->format('Y-m-d')
            )
        );
    }
}
