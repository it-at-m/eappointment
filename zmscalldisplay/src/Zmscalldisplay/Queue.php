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

class Queue extends BaseController
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
        $validator = $request->getAttribute('validator');
        $displayNumber = $validator->getParameter('display')->isNumber()->getValue();

        $calldisplay = new Helper\Calldisplay($request);

        $queueList = \App::$http
            ->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false), [
                'statusList' => $calldisplay::getRequestedQueueStatus($request)
            ])
            ->getCollection();

        $queueList = ($queueList) ?
            $queueList->withStatus($calldisplay::getRequestedQueueStatus($request))->sortByCallTime('descending') :
            new \BO\Zmsentities\Collection\QueueList();

        if ($displayNumber === 1) {
            $queueList = $queueList->chunk(10)[0];
        } else if ($displayNumber === 2) {
            $queueList = $queueList->chunk(10)[1] ?? new \BO\Zmsentities\Collection\QueueList();
        }

        $displayInfo = null;
        $callDisplayInfo = $calldisplay->getEntity(true);

        if ($callDisplayInfo->getClusterList()->count() > 0 && $callDisplayInfo->getClusterList()->getFirst()->callDisplayText) {
            $displayInfo = $callDisplayInfo->getClusterList()->getFirst()->callDisplayText;
        } else if (
            $callDisplayInfo->getScopeList()->count() > 0
            && $callDisplayInfo->getScopeList()->getFirst()->preferences['queue']['callDisplayText']
        ) {
            $displayInfo = $callDisplayInfo->getScopeList()->getFirst()->preferences['queue']['callDisplayText'];
        }

        return Render::withHtml(
            $response,
            'block/queue/queueTable.twig',
            array(
                'text' => 'asdsadsa',
                'tableSettings' => $validator->getParameter('tableLayout')->isArray()->getValue(),
                'calldisplay' => $calldisplay->getEntity(false),
                'scope' => $calldisplay->getSingleScope(),
                'queueList' => $queueList,
                'displayInfo' => $displayInfo
            )
        );
    }
}
