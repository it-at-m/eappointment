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
            $queueList->withStatus($calldisplay::getRequestedQueueStatus($request)) :
            new \BO\Zmsentities\Collection\QueueList();

        if ($displayNumber === 1) {
            $queueList = $queueList->chunk(10)[0];
        } else if ($displayNumber === 2) {
            $queueList = $queueList->chunk(10)[1] ?? new \BO\Zmsentities\Collection\QueueList();
        }

        return Render::withHtml(
            $response,
            'block/queue/queueTable.twig',
            array(
                'tableSettings' => $validator->getParameter('tableLayout')->isArray()->getValue(),
                'calldisplay' => $calldisplay->getEntity(false),
                'scope' => $calldisplay->getSingleScope(),
                'queueList' => $queueList
            )
        );
    }
}
