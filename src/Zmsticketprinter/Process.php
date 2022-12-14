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
        $config = \App::$http->readGetResult('/config/', [], \App::SECURE_TOKEN)->getEntity();
        $validator = $request->getAttribute('validator');
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $clusterId = $validator->getParameter('clusterId')->isNumber()->getValue();
        $ticketprinter = Helper\Ticketprinter::readWithHash($request);


        if ($scopeId) {
            $process = \App::$http->readGetResult(
                '/scope/'. $scopeId .'/waitingnumber/'. $ticketprinter->hash .'/'
            )->getEntity();
        } elseif ($clusterId) {
            $process = \App::$http->readGetResult(
                '/cluster/'. $clusterId .'/waitingnumber/'. $ticketprinter->hash .'/'
            )->getEntity();
            $scope = $process->scope;
        } else {
            throw new BadRequest('Missing Parameter');
        }

        $scope = new Scope($process->scope);
        $department = \App::$http->readGetResult('/scope/'. $scope->getId() . '/department/')->getEntity();

        $queueListHelper = (new QueueListHelper($scope, $process));

        return Render::withHtml(
            $response,
            'page/process.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinter,
                'organisation' => \App::$http->readGetResult(
                    '/scope/'. $scope->id . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity(),
                'process' => $process,
                'waitingTime' => $queueListHelper->getEstimatedWaitingTime(),
                'waitingClients' => ($queueListHelper->getClientsBefore()),
                'config' => $config,
                'department' => $department
            )
        );
    }
}
