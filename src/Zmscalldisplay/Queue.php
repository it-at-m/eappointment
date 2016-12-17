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

        $calldisplay = (new Helper\Calldisplay($request))->getEntity();
        $queueList = \App::$http->readPostResult('/calldisplay/queue/', $calldisplay)->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/queue/queueTable.twig',
            array(
                'tableSettings' => $validator->getParameter('tableLayout')->isArray()->getValue(),
                'calldisplay' => $calldisplay,
                'queueList' => $queueList->withStatus('called'),
                'waitingClients' => $queueList->count(),
                'waitingTime' => $queueList->getLast()->waitingTimeEstimate
            )
        );
    }
}
