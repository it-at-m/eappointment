<?php
/**
 *
 * @package Zmsappointment
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
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');

        $calldisplay = new Helper\Calldisplay($request);
        //error_log(json_encode($calldisplay->getEntity(false)));
        $queueList = \App::$http->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false))
            ->getCollection();
        $queueList = ($queueList) ?
            $queueList->withStatus($calldisplay::getRequestedQueueStatus($request)) :
            new \BO\Zmsentities\Collection\QueueList();
        $lastItem = $queueList->getLast();
        $waitingTime = 0;
        if ($lastItem) {
            $waitingTime = $lastItem->waitingTimeEstimate;
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/queueTable.twig',
            array(
                'tableSettings' => $validator->getParameter('tableLayout')->isArray()->getValue(),
                'calldisplay' => $calldisplay->getEntity(false),
                'queueList' => $queueList,
                'waitingClients' => $queueList->count(),
                'waitingTime' => $waitingTime,
            )
        );
    }
}
