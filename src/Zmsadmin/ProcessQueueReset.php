<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Slim\Render;

/**
 * Queue a process
 */
class ProcessQueueReset extends BaseController
{

    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $validator = $request->getAttribute('validator');
        $processId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        if ($processId) {
            $selectedProcess = \App::$http->readGetResult('/workstation/process/'. $processId .'/get/')->getEntity();
        }
        $queuedProcess = \App::$http->readPostResult('/process/status/queued/', $selectedProcess)->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/queued.twig',
            array(
                'title' => 'Termin zurücksetzen',
                'process' => $queuedProcess,
                'workstation' => $workstation
            )
        );
    }
}
