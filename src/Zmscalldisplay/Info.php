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
        ResponseInterface $response,
        array $args
    ) {
        $calldisplay = new Helper\Calldisplay($request);
        $queueListFull = \App::$http
            ->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false))
            ->getCollection()
            ->withStatus(['called','confirmed', 'queued', 'reserved', 'deleted', 'fake', 'pickup', 'processing']);

        $fakeEntity = $queueListFull->getFakeOrLastWaitingnumber();
        $waitingClientsBefore = $queueListFull->getQueuePositionByNumber($fakeEntity->number);

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
