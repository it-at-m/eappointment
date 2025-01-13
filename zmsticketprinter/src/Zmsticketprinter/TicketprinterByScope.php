<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsticketprinter;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsticketprinter\Helper\QueueListHelper;
use BO\Zmsticketprinter\Helper\TemplateFinder;

class TicketprinterByScope extends BaseController
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
        $department = \App::$http->readGetResult('/scope/' . $scope->id . '/department/')->getEntity();
        $organisation = $ticketprinterHelper->getOrganisation();

        $queueListHelper = new QueueListHelper($scope);

        $template = (new TemplateFinder($defaultTemplate))->setCustomizedTemplate($ticketprinter, $organisation);

        $hasDisabledButton = false;
        foreach ($ticketprinter->buttons as $button) {
            if (!isset($button['enabled']) || $button['enabled'] != 1) {
                $hasDisabledButton = true;
                break;
            }
        }

        return Render::withHtml(
            $response,
            $template->getTemplate(),
            array(
                'debug' => \App::DEBUG,
                'enabled' => $ticketprinter->isEnabled()
                    || !$organisation->getPreference('ticketPrinterProtectionEnabled'),
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $organisation,
                'department' => $department,
                'scope' => $scope,
                'queueList' => $queueListHelper->getList(),
                'waitingTime' => $queueListHelper->getEstimatedWaitingTime(),
                'waitingTimeOptimistic' => $queueListHelper->getOptimisticWaitingTime(),
                'waitingClients' => $queueListHelper->getClientsBefore(),
                'buttonDisplay' => $template->getButtonTemplateType($ticketprinter),
                'config' => $config,
                'hasDisabledButton' => $hasDisabledButton
            )
        );
    }
}
