<?php

/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmscalldisplay;

use BO\Slim\Render;
use BO\Zmsclient\Exception as ZmsclientException;
use BO\Zmsclient\Psr7\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsentities\Collection\QueueList;

class Info extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     * @psalm-api
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $calldisplay = new Helper\Calldisplay($request);
        try {
            $queueListFull = \App::$http
                ->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false))
                ->getCollection();
        } catch (ZmsclientException | RequestException $exception) {
            return $this->skipRefresh($response, $exception, 'Calldisplay waitinginfo refresh skipped');
        }

        $filteredQueue = $queueListFull
            ->withoutStatus(QueueList::STATUS_FAKE)
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
