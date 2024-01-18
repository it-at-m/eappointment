<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \Psr\Http\Message\RequestInterface;

class WorkstationProcessRedirect extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $department = \App::$http
            ->readGetResult(
                '/scope/' . $workstation->scope['id'] . '/department/',
                ['resolveReferences' => 2]
            )->getEntity();
        $input = $request->getParsedBody();
        $process = $workstation->process;

        if ($request->getMethod() === 'POST') {
            $validator = $request->getAttribute('validator');
            $selectedLocation = $validator->getParameter('location')->isNumber()->getValue();

            if (empty($selectedLocation)) {
                return \BO\Slim\Render::redirect(
                    'workstationProcessRedirect',
                    [],
                    [
                        'errors' => [
                            'location' => 'Bitte wählen Sie einen Standort aus.'
                        ]
                    ]
                );
            }

            $scope = \App::$http
                ->readGetResult(
                    '/scope/' . $selectedLocation . '/',
                    ['resolveReferences' => 2]
                )->getEntity();

            $process = \App::$http
                ->readGetResult(
                    '/process/'. $process->getId() .'/'. $process->getAuthKey() .'/',
                    ['resolveReferences' => 2]
                )->getEntity();

            $newProcess = clone $process;
            $newProcess->scope = $scope;
            $newProcess->appointments[0]->scope = $scope;
            $newProcess->amendment = $input['amendment'];

            $process = \App::$http->readPostResult('/process/status/redirect/', $newProcess)->getEntity();

            return \BO\Slim\Render::redirect(
                $workstation->getVariantName(),
                array(),
                array()
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstationProcessRedirect.twig',
            array(
                'title' => 'Kundendaten',
                'workstation' => $workstation,
                'department' => $department,
                'scope' => $workstation->scope,
                'scopes' => $department->getScopeList(),
                'menuActive' => 'workstation',
                'errors' => $request->getParam('errors')
            )
        );
    }
}
