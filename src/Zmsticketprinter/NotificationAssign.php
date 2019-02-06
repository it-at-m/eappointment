<?php
/**
 *
 * @package Zmsticketprinter
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
    public function readResponse(
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

        $process = $this->writeUpdateProcessWithNotification($process, $telephone);

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

    protected function writeUpdateProcessWithNotification(\BO\Zmsentities\Process $process, $telephone)
    {
        //update process client data
        $client = $process->getClients()->getFirst();
        $client->telephone = $telephone->getValue();
        //update calculated reminderTimestamp
        $headsUpTime = $process->getCurrentScope()->getPreference('notifications', 'headsUpTime');
        $queue = $process->queue;
        $number = ($queue->withAppointment) ? $process->getId() : $queue->number;
        $waitingTimeEstimate = (new Helper\QueueListHelper($process->getCurrentScope()))
            ->getList()
            ->getQueueByNumber($number)
            ->waitingTimeEstimate;
        $process->reminderTimestamp = $queue->arrivalTime + ($waitingTimeEstimate * 60) - ($headsUpTime * 60);

        //add notification to queue
        $process->status = 'queued';
        $process = \App::$http
            ->readPostResult('/process/'. $process->id .'/'. $process->authKey .'/', $process)
            ->getEntity();

        \App::$http->readPostResult(
            '/process/'. $process->id .'/'. $process->authKey .'/confirmation/notification/',
            $process
        );
        return $process;
    }
}
