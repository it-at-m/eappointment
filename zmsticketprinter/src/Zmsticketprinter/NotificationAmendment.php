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

class NotificationAmendment extends BaseController
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
        $validator = $request->getAttribute('validator');
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $clusterId = $validator->getParameter('clusterId')->isNumber()->getValue();
        $ticketprinter = (new Helper\Ticketprinter($args, $request))->getEntity();

        $scope = ($scopeId) ? \App::$http->readGetResult('/scope/' . $scopeId . '/')->getEntity() : null;
        $cluster = ($clusterId) ? \App::$http->readGetResult('/cluster/' . $clusterId . '/')->getEntity() : null;

        return Render::withHtml(
            $response,
            'page/notificationAmendment.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinter,
                'scope' => $scope,
                'cluster' => $cluster,
                'organisation' => \App::$http->readGetResult(
                    '/scope/' . $scopeId . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity(),
            )
        );
    }
}
