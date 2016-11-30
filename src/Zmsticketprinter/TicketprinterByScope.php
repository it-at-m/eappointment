<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class TicketprinterByScope extends BaseController
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
        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));
        $ticketprinter = $ticketprinterHelper->getEntity();

        $queueList = \App::$http->readGetResult('/scope/'. $args['scopeId'] . '/queue/')->getCollection();
        $scope = $ticketprinter->getScopeList()->getFirst();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/buttonSingleRow_default.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $ticketprinterHelper::$organisation,
                'queueList' => $queueList,
                'scope' => $scope,
                'estimatedData' => $scope->getWaitingTimeFromQueueList($queueList, \App::$now)
            )
        );
    }
}
