<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use BO\Slim\Render;
use BO\Zmsclient\Exception\BadRequest;
use BO\Zmsentities\Scope;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsticketprinter\Helper\QueueListHelper;

class Process extends BaseController
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
        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));

        $config = \App::$http->readGetResult('/config/', [], \App::SECURE_TOKEN)->getEntity();
        $validator = $request->getAttribute('validator');
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $requestId = $validator->getParameter('requestId')->isNumber()->getValue();
        if (null === $scopeId) {
            throw new Exception\ScopeNotFound();
        }

        $process = \App::$http->readGetResult(
            '/scope/'. $scopeId .'/waitingnumber/'. $ticketprinterHelper->getEntity()->hash .'/',
            $requestId ? ['requestId' => $requestId] : null
        )->getEntity();

        $scope = new Scope($process->scope);
        $department = \App::$http->readGetResult('/scope/'. $scope->getId() . '/department/')->getEntity();

        $queueListHelper = (new QueueListHelper($scope, $process));

        return Render::withHtml(
            $response,
            'page/process.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinterHelper->getEntity(),
                'organisation' => $ticketprinterHelper->getOrganisation(),
                'process' => $process,
                'waitingTime' => $queueListHelper->getEstimatedWaitingTime(),
                'waitingClients' => ($queueListHelper->getClientsBefore()),
                'config' => $config,
                'department' => $department
            )
        );
    }
}
