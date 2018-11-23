<?php
/**
 *
 * @package Zmsticketprinter
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
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        Helper\HomeUrl::create($request);

        $config = \App::$http->readGetResult('/config/', [], \App::SECURE_TOKEN)->getEntity();
        $validator = $request->getAttribute('validator');
        $defaultTemplate = $validator->getParameter("template")
            ->isPath()
            ->setDefault('default')
            ->getValue();
        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));
        $ticketprinter = $ticketprinterHelper->getEntity();
        $scope = $ticketprinter->getScopeList()->getFirst();
        $department = \App::$http->readGetResult('/scope/'. $scope->id . '/department/')->getEntity();
        $organisation = $ticketprinterHelper::$organisation;
        
        $queueListHelper = (new Helper\QueueListHelper($scope, \App::$now));
        
        $template = (new Helper\TemplateFinder($defaultTemplate))->setCustomizedTemplate($ticketprinter, $organisation);

        return \BO\Slim\Render::withHtml(
            $response,
            $template->getTemplate(),
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $organisation,
                'department' => $department,
                'scope' => $scope,
                'queueList' => $queueListHelper->getList(),
                'waitingTime' => $queueListHelper->getEstimatedWaitingTime(),
                'waitingClients' => $queueListHelper->getList()->count(),
                'buttonDisplay' => $template->getButtonTemplateType($ticketprinter),
                'config' => $config
            )
        );
    }
}
