<?php
/**
 *
 * @package Zmsticketprinter
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
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $waitingNumber = $validator->getParameter('waitingNumber')->isNumber()->getValue();
        $hasProcess = $validator->getParameter('processWithNotifiation')->isNumber()->getValue();
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $ticketprinter = Helper\Ticketprinter::readWithHash($request);

        if (! $waitingNumber) {
            return \BO\Slim\Render::redirect(
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

        if ($scopeId) {
            $process = \App::$http
                ->readGetResult('/scope/'. $scopeId .'/queue/'. $waitingNumber .'/')
                ->getEntity();
        } else {
            throw new Exception\ScopeNotFound();
        }
        if ($process->getFirstClient()->hasTelephone()) {
            return \BO\Slim\Render::redirect(
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
                    '/scope/'. $scopeId . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity(),
                'process' => $process
            )
        );
    }
}
