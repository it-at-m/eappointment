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
        $initiator = Validator::param('initiator')->isString()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/')->getEntity();
        $process->status = 'deleted';
        $workstation->testMatchingProcessScope((new Helper\ClusterHelper($workstation))->getScopeList(), $process);

        \App::$http->readDeleteResult('/process/'. $process->getId() .'/', ['initiator' => $initiator]);
        static::writeDeleteMailNotifications($process);

        return \BO\Slim\Render::withHtml(
            $response,
            'element/helper/messageHandler.twig',
            array(
                'success' => 'process_deleted',
                'selectedprocess' => $process,
            )
        );
    }

    public static function writeDeleteMailNotifications($process)
    {
        #email only for clients with appointment if email address is given
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
        #sms notifications for clients with and without appointment if telephone number is given
        if ($process->scope->hasNotificationEnabled() &&
            $process->getFirstClient()->hasTelephone()
        ) {
            \App::$http
                ->readPostResult(
                    '/process/'. $process->getId() .'/'. $process->getAuthKey() .'/delete/notification/',
                    $process
                )->getEntity();
        }
    }
}
