<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use BO\Zmsdb\Department;
use BO\Zmsentities\Collection\QueueList;
use \BO\Zmsentities\Helper\DateTime;

class UserQueue extends BaseController
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
        (new Helper\User($request))->checkRights('basic');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $statusParameter = Validator::param('status')->isString()->getValue();
        $statuses = empty($statusParameter) ? [] : explode(',', $statusParameter);
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? (new DateTime($selectedDate))->modify(\App::$now->format('H:i')) : \App::$now;

        $message = Response\Message::create($request);

        $workstation = (new Helper\User($request, 2))->checkRights();
        $queueList = new QueueList();
        foreach ($workstation->getUseraccount()['departments'] as $department) {
            $queueList->addList((new Department())->readQueueList($department->id, $dateTime));
        }
        $queues = $queueList->withSortedWaitingTime();

        $message->data = $queues;

        if ($resolveReferences > 1) {
            $filteredQueues = [];
            foreach ($queues as $queue) {
                if (! empty($statuses) && ! in_array($queue->status, $statuses)) {
                    continue;
                }

                $queue->process = $queue->getProcess();
                $filteredQueues[] = $queue;
            }

            $message->data = $filteredQueues;
        }

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
