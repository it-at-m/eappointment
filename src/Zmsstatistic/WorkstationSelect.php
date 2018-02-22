<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use \BO\Zmsstatistic\Helper\LoginForm;
use \BO\Mellon\Validator;

class WorkstationSelect extends BaseController
{
    protected $resolveLevel = 3;
    /**
     * @SuppressWarnings(Parameter)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $this->workstation->testDepartmentList();

        $input = $request->getParsedBody();
        $formData = [];
        if (is_array($input) && (array_key_exists('scope', $input))) {
            $form = LoginForm::fromAdditionalParameters();
            $formData = $form->getStatus();
            $selectedDate = Validator::param('selectedDate')->isString()->getValue();
            $queryParams = ($selectedDate) ? array('date' => $selectedDate) : array();
            if (! $form->hasFailed()) {
                LoginForm::writeWorkstationUpdate($form, $this->workstation);
                return \BO\Slim\Render::redirect(
                    'Overview',
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
                'workstation' => $this->workstation,
                'menuActive' => 'select',
                'today' => \App::$now->format('Y-m-d')
            )
        );
    }
}
