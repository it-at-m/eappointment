<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsstatistic\Helper\LoginForm;
use BO\Mellon\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WorkstationSelect extends BaseController
{
    protected $resolveLevel = 3;
    /**
     * @SuppressWarnings(Parameter)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        /** @var \BO\Zmsentities\Workstation $workstation */
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3])->getEntity();
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect('index', array('error' => 'login_failed'));
        }
        $input = $request->getParsedBody();
        $formData = [];
        if (is_array($input) && (array_key_exists('scope', $input))) {
            $form = LoginForm::fromAdditionalParameters();
            $formData = $form->getStatus();
            $selectedDate = Validator::param('selectedDate')->isString()->getValue();
            $queryParams = ($selectedDate) ? array('date' => $selectedDate) : array();
            if (! $form->hasFailed()) {
                LoginForm::writeWorkstationUpdate($form, $this->workstation);
                return Render::redirect(
                    'Overview',
                    array(),
                    $queryParams
                );
            }
        }

        return Render::withHtml(
            $response,
            'page/workstationSelect.twig',
            array(
                'title' => 'Standort auswählen',
                'advancedData' => $formData,
                'workstation' => $this->workstation,
                'menuActive' => 'select',
                'today' => \App::$now->format('Y-m-d')
            )
        );
    }
}
