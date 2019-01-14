<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

/**
 * Handle requests concerning services
 */
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
            ->getCollection();
        $queueList = ($queueListFull) ?
            $queueListFull->withStatus($calldisplay::getRequestedQueueStatus($request)) :
            new \BO\Zmsentities\Collection\QueueList();
        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/queueTable.twig',
            array(
                'tableSettings' => $validator->getParameter('tableLayout')->isArray()->getValue(),
                'calldisplay' => $calldisplay->getEntity(false),
                'scope' => $calldisplay->getSingleScope(),
                'queueList' => $queueList,
                'waitingClients' => $queueListFull->withoutStatus(['called'])->count(),
                'waitingTime' => $queueListFull->getLast()->waitingTimeEstimate,
                'waitingTimeOptimistic' => $queueListFull->getLast()->waitingTimeOptimistic,
            )
        );
    }
}
