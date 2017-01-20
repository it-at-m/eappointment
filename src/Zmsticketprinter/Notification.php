<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class Notification extends BaseController
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
        $waitingNumber = $validator->getParameter('waitingNumber')->isNumber()->getValue();
        $hasProcess = $validator->getParameter('processWithNotifiation')->isNumber()->getValue();
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $clusterId = $validator->getParameter('clusterId')->isNumber()->getValue();
        $ticketprinter = Helper\Ticketprinter::readWithHash($request);

        if (! $waitingNumber) {
            return \BO\Slim\Render::redirect(
                'Message',
                [],
                [
                    'status' => 'process_notification_amendment_waitingnumber_unvalid',
                    'scopeId' => $scopeId,
                    'notHome' => 1
                ]
            );
        }

        if ($scopeId) {
            $process = \App::$http
                ->readGetResult('/process/queue/'. $waitingNumber .'/scope/'. $scopeId .'/')
                ->getEntity();
        } elseif ($clusterId) {
            $process = \App::$http
                ->readGetResult('/process/queue/'. $waitingNumber .'/cluster/'. $clusterId .'/')
                ->getEntity();
            $scopeId = $process->getScopeId();
        } else {
            throw new Exception\ScopeAndClusterNotFound();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/notification.twig',
            array(
                'debug' => \App::DEBUG,
                'hasProcess' => $hasProcess,
                'homeUrl' => \BO\Zmsclient\Ticketprinter::getHomeUrl(),
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinter,
                'organisation' => \App::$http->readGetResult(
                    '/organisation/scope/'. $scopeId . '/',
                    ['resolveReferences' => 2]
                )->getEntity(),
                'process' => $process
            )
        );
    }
}
