<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class Message extends BaseController
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
        $ticketprinter = Helper\Ticketprinter::readWithHash($request);

        $validator = $request->getAttribute('validator');
        $validateStatus = $validator->getParameter('status')->isString()->getValue();
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $notHome = $validator->getParameter('notHome')->isNumber()->getValue();

        $messages = explode(',', $validateStatus);

        return \BO\Slim\Render::withHtml(
            $response,
            'page/message.twig',
            array(
                'debug' => \App::DEBUG,
                'homeRedirect' => ($notHome) ? false : true,
                'title' => 'Wartennumernausgabe erfolgreich',
                'ticketprinter' => $ticketprinter,
                'scopeId' => $scopeId,
                'organisation' => \App::$http->readGetResult('/organisation/scope/'. $scopeId . '/')->getEntity(),
                'messages' => $messages
            )
        );
    }
}
