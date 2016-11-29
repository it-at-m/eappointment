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
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $clusterId = $validator->getParameter('clusterId')->isNumber()->getValue();
        $ticketprinter = Helper\Ticketprinter::readWithHash($request);

        if ($waitingNumber && $scopeId) {
            $process = \App::$http
                ->readGetResult('/process/queue/'. $waitingNumber .'/scope/'. $scopeId .'/')
                ->getEntity();
        } elseif ($waitingNumber && $clusterId) {
            $process = \App::$http
                ->readGetResult('/process/queue/'. $waitingNumber .'/cluster/'. $clusterId .'/')
                ->getEntity();
        } elseif ($scopeId) {
            $process = \App::$http
                ->readGetResult('/scope/'. $scopeId .'/waitingnumber/'. $ticketprinter->hash .'/')
                ->getEntity();
        } elseif ($clusterId) {
            $process = \App::$http
                ->readGetResult('/cluster/'. $clusterId .'/waitingnumber/'. $ticketprinter->hash .'/')
                ->getEntity();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/notification.twig',
            array(
                'debug' => \App::DEBUG,
                'homeUrl' => \BO\Zmsclient\Ticketprinter::getHomeUrl(),
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinter,
                'organisation' => \App::$http->readGetResult('/organisation/scope/'. $scopeId . '/')->getEntity(),
                'process' => $process
            )
        );
    }
}
