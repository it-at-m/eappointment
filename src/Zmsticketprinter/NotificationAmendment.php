<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class NotificationAmendment extends BaseController
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
        $validator = $request->getAttribute('validator');
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $clusterId = $validator->getParameter('clusterId')->isNumber()->getValue();
        $ticketprinter = Helper\Ticketprinter::readWithHash($request);

        $scope = ($scopeId) ? \App::$http->readGetResult('/scope/'. $scopeId .'/')->getEntity() : null;
        $cluster = ($clusterId) ? \App::$http->readGetResult('/cluster/'. $clusterId .'/')->getEntity() : null;

        return \BO\Slim\Render::withHtml(
            $response,
            'page/notificationAmendment.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinter,
                'scope' => $scope,
                'cluster' => $cluster,
                'organisation' => \App::$http->readGetResult(
                    '/scope/'. $scopeId . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity(),
            )
        );
    }
}
