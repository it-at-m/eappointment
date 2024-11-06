<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsentities\Collection\QueueList as Collection;

class Info extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $calldisplay = new Helper\Calldisplay($request);
        $queueListFull = \App::$http
            ->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false))
            ->getCollection();

        $filteredQueue = $queueListFull
            ->withoutStatus(Collection::STATUS_FAKE)
            ->getCountWithWaitingTime();

        $lastClient = $filteredQueue->getLast();
        $waitingTimeFull = $lastClient ? $lastClient->waitingTimeEstimate : 0;
        $waitingTimeOptimistic = $lastClient ? $lastClient->waitingTimeOptimistic : 0;

        return Render::withHtml(
            $response,
            'element/tempWaitingValues.twig',
            array(
                'calldisplay' => $calldisplay,
                'waitingClients' => $filteredQueue->count(),
                'waitingTime' => $waitingTimeFull,
                'waitingTimeOptimistic' => $waitingTimeOptimistic
            )
        );
    }
}