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
        $fakeEntity = $queueListFull->getFakeOrLastWaitingnumber();
        $waitingClientsBefore = $queueListFull->getQueuePositionByNumber($fakeEntity->number) + 1;

        return Render::withHtml(
            $response,
            'element/tempWaitingValues.twig',
            array(
                'calldisplay' => $calldisplay,
                'waitingClients' => $waitingClientsBefore,
                'waitingTime' => $fakeEntity->waitingTimeEstimate,
                'waitingTimeOptimistic' => $fakeEntity->waitingTimeOptimistic,
            )
        );
    }
}
