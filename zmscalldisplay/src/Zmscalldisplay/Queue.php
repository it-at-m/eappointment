<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

use BO\Slim\Render;
use BO\Zmsentities\Schema\Entity;
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

        $calldisplay = new Helper\Calldisplay($request);

        $queueList = \App::$http
            ->readPostResult('/calldisplay/queue/', $calldisplay->getEntity(false), [
                'statusList' => $calldisplay::getRequestedQueueStatus($request)
            ])
            ->getCollection();

        return Render::withHtml(
            $response,
            'block/queue/queueTable.twig',
            array(
                'tableSettings' => $validator->getParameter('tableLayout')->isArray()->getValue(),
                'calldisplay' => $calldisplay->getEntity(false),
                'scope' => $calldisplay->getSingleScope(),
                'queueList' => ($queueList) ?
                    $queueList->withStatus($calldisplay::getRequestedQueueStatus($request)) :
                    new \BO\Zmsentities\Collection\QueueList()
            )
        );
    }
}
