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

class Notification extends BaseController
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
        $waitingNumber = $validator->getParameter('waitingNumber')->isNumber()->getValue();
        $hasProcess = $validator->getParameter('processWithNotifiation')->isNumber()->getValue();
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        if (null === $scopeId) {
            throw new Exception\ScopeNotFound();
        }
        $ticketprinterHelper = new Helper\Ticketprinter($args, $request);

        if (! $waitingNumber) {
            return Render::redirect(
                'Message',
                [
                  'status' => 'process_notification_amendment_waitingnumber_unvalid'
                ],
                [
                    'scopeId' => $scopeId,
                    'notHome' => 1
                ]
            );
        }

        $process = \App::$http
            ->readGetResult('/scope/' . $scopeId . '/queue/' . $waitingNumber . '/')
            ->getEntity();

        if ($process->getFirstClient()->hasTelephone()) {
            return Render::redirect(
                'Message',
                [
                  'status' => 'process_notification_amendment_phonenumber_exists'
                ],
                [
                    'scopeId' => $scopeId,
                    'notHome' => 1
                ]
            );
        }

        return Render::withHtml(
            $response,
            'page/notification.twig',
            array(
                'debug' => \App::DEBUG,
                'hasProcess' => $hasProcess,
                'homeUrl' => \BO\Zmsclient\Ticketprinter::getHomeUrl(),
                'title' => 'Anmeldung an der Warteschlange',
                'ticketprinter' => $ticketprinterHelper->getEntity(),
                'organisation' => $ticketprinterHelper->getOrganisation(),
                'process' => $process
            )
        );
    }
}
