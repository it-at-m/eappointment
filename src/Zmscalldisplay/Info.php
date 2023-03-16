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

        $waitingClientsBefore = $queueListFull
            ->withoutStatus(Collection::STATUS_FAKE)
            ->getCountWithWaitingTime()
            ->count();

        $waitingTimeFull = $queueListFull
            ->withoutStatus(Collection::STATUS_FAKE)
            ->getCountWithWaitingTime()
            ->getLast()
            ->waitingTimeEstimate;

        $waitingTimeOptim = $queueListFull
            ->withoutStatus(Collection::STATUS_FAKE)
            ->getCountWithWaitingTime()
            ->getLast()
            ->waitingTimeOptimistic;

        return Render::withHtml(
            $response,
            'element/tempWaitingValues.twig',
            array(
                'calldisplay' => $calldisplay,
                'waitingClients' => $waitingClientsBefore,
                'waitingTime' => $waitingTimeFull,
                'waitingTimeOptimistic' => $waitingTimeOptim,
            )
        );
    }
}
