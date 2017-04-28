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
 * Delete a process
 */
class PickupMail extends BaseController
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
        $process = \App::$http->readGetResult('/workstation/process/'. $processId .'/get/')->getEntity();
        $process->status = 'pickup';
        $cluster = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/cluster/')->getEntity();
        $workstation->testMatchingProcessScope($cluster, $process);

        \App::$http->readPostResult('/mails/', $process);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/mailSent.twig',
            array(
                'process' => $process
            )
        );
    }
}
