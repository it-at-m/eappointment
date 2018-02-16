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
class ProcessDelete extends BaseController
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
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        $process->status = 'deleted';
        $workstation->testMatchingProcessScope((new Helper\ClusterHelper($workstation))->getScopeList(), $process);
        $authKey = $process->authKey;

        $initiator = Validator::param('initiator')->isString()->getValue();
        \App::$http
            ->readDeleteResult('/process/'. $process->id .'/', ['initiator' => $initiator])
            ->getEntity();

        if ($process->getFirstClient()->hasEmail()) {
            \App::$http->readPostResult('/process/'. $process->id .'/'. $authKey .'/delete/mail/', $process);
        }
        if ($process->scope->getPreference('appointment', 'notificationConfirmationEnabled') &&
            $process->getFirstClient()->hasTelephone()
        ) {
            \App::$http->readPostResult('/process/'. $process->id .'/'. $authKey .'/delete/notification/', $process);
        }

        return \BO\Slim\Render::redirect(
            'appointment_form',
            array(),
            array(
                'selectedprocess' => $process->getId(),
                'success' => 'process_deleted'
            )
        );
    }
}
