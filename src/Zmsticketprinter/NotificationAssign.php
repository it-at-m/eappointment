<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class NotificationAssign extends BaseController
{

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $telephone = $validator->getParameter('telephone')->isString()->isBiggerThan(10);
        //get process
        $processId = $validator->getParameter('processId')->isNumber()->getValue();
        $authKey = $validator->getParameter('authKey')->isString()->getValue();
        $process = \App::$http->readGetResult('/process/'. $processId .'/'. $authKey .'/')->getEntity();

        if ($telephone->hasFailed()) {
            return \BO\Slim\Render::redirect(
                'Message',
                [
                    'status' => 'process_notification_number_unvalid'
                ],
                [
                    'scopeId' => $process->getScopeId(),
                    'notHome' => 1
                ]
            );
        }

        //update process client data
        $client = $process->getClients()->getFirst();
        $client->telephone = $telephone->getValue();
        $process = \App::$http
            ->readPostResult('/process/'. $process->id .'/'. $process->authKey .'/', $process)
            ->getEntity();



        //add notification to queue
        $process->status = 'queued';
        \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
            $process
        );

        return \BO\Slim\Render::redirect(
            'Message',
            [
                'status' => 'process_notification_success'
            ],
            [
                'scopeId' => $process->getScopeId()
            ]
        );
    }
}
