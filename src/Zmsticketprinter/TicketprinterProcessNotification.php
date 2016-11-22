<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class TicketprinterProcessNotification extends BaseController
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
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/process.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinter,
                'organisation' => \App::$http->readGetResult('/organisation/scope/'. $scopeId . '/')->getEntity(),
                'process' => $process
            )
        );
    }
}
