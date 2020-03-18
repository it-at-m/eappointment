<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

class Queue extends BaseController
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

        $calldisplay = new Helper\Calldisplay($request);

        $queueListFull = \App::$http
            ->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false))
            ->getCollection()->withStatus(['called','confirmed', 'queued', 'reserved', 'deleted', 'fake', 'pickup']);
        $queueList = ($queueListFull) ?
            $queueListFull->withStatus($calldisplay::getRequestedQueueStatus($request)) :
            new \BO\Zmsentities\Collection\QueueList();

        $fakeEntity = $queueListFull->getFakeOrLastWaitingnumber();
        $waitingClientsBefore = $queueListFull->getQueuePositionByNumber($fakeEntity->number);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/queueTable.twig',
            array(
                'tableSettings' => $validator->getParameter('tableLayout')->isArray()->getValue(),
                'calldisplay' => $calldisplay->getEntity(false),
                'scope' => $calldisplay->getSingleScope(),
                'queueList' => $queueList,
                'waitingClients' => $waitingClientsBefore,
                'waitingTime' => $fakeEntity->waitingTimeEstimate,
                'waitingTimeOptimistic' => $fakeEntity->waitingTimeOptimistic,
            )
        );
    }
}
