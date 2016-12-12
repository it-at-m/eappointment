<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

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
        Helper\HomeUrl::create($request);

        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));
        $ticketprinter = $ticketprinterHelper->getEntity();
        $scope = $ticketprinter->getScopeList()->getFirst();
        $organisation = $ticketprinterHelper::$organisation;
        $queueList = \App::$http->readGetResult('/scope/'. $args['scopeId'] . '/queue/')->getCollection();
        $estimatedData = ($queueList) ? $scope->getWaitingTimeFromQueueList($queueList, \App::$now) : null;

        $template = Helper\TemplateFinder::getCustomizedTemplate($ticketprinter, $organisation);

        return \BO\Slim\Render::withHtml(
            $response,
            $template,
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $organisation,
                'queueList' => $queueList,
                'scope' => $scope,
                'estimatedData' => $estimatedData,
                'buttonDisplay' => Helper\TemplateFinder::getButtonTemplateType($ticketprinter)
            )
        );
    }
}
