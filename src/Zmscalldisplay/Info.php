<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

class Info extends BaseController
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
        $calldisplay = new Helper\Calldisplay($request);
        $queueListFull = \App::$http
            ->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false))
            ->getCollection()
            ->withStatus(['called','confirmed', 'queued', 'reserved', 'deleted', 'fake', 'pickup', 'processing']);

        $fakeEntity = $queueListFull->getFakeOrLastWaitingnumber();
        $waitingClientsBefore = $queueListFull->getQueuePositionByNumber($fakeEntity->number);

        return \BO\Slim\Render::withHtml(
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
