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

        $initiator = Validator::param('initiator')->isString()->getValue();
        \App::$http->readDeleteResult('/process/'. $process->getId() .'/', ['initiator' => $initiator])->getEntity();

        $this->testDeleteMailNotifications($process);

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            array(
                'success' => 'process_deleted',
            )
        );
    }

    protected function testDeleteMailNotifications($process)
    {
        if ($process->getFirstClient()->hasEmail() &&
            $process->isWithAppointment() &&
            $process->scope->hasEmailFrom()
        ) {
            \App::$http
                ->readPostResult(
                    '/process/'. $process->getId() .'/'. $process->getAuthKey() .'/delete/mail/',
                    $process
                )->getEntity();
        }
        if ($process->scope->hasNotificationEnabled() &&
            $process->getFirstClient()->hasTelephone() &&
            $process->isWithAppointment()
        ) {
            \App::$http
                ->readPostResult(
                    '/process/'. $process->getId() .'/'. $process->getAuthKey() .'/delete/notification/',
                    $process
                )->getEntity();
        }
    }
}
