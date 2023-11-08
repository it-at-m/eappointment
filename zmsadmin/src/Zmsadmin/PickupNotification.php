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
use BO\Zmsentities\Notification as Entity;

/**
 * Delete a process
 */
class PickupNotification extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $validator = $request->getAttribute('validator');
        $processId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        // disabled 2021-01-20 by TK because in ZMS1 it is allowed to send pickup notifications
        // to processes from another scope in department
        //$workstation->testMatchingProcessScope($workstation->getScopeList(), $process);
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();
        $config = \App::$http->readGetResult('/config/')->getEntity();

        if ($process->scope->hasNotificationEnabled()) {
            $notification = (new Entity)->toResolvedEntity($process, $config, $department, 'pickup');
            $notification = \App::$http->readPostResult('/notification/', $notification)->getEntity();
        }
    
        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/notificationSent.twig',
            array(
               'process' => $process,
               'notification' => $notification ? $notification : null
            )
        );
    }
}
