<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsticketprinter;

use BO\Mellon\Validator;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Message extends BaseController
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
        $validator = $request->getAttribute('validator');
        $scopeId = $validator->getParameter('scopeId')->isNumber()->getValue();
        $notHome = $validator->getParameter('notHome')->isNumber()->getValue();
        $messages = Validator::value($args['status'])->isString()->getValue();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/message.twig',
            array(
                'debug' => \App::DEBUG,
                'homeRedirect' => ($notHome) ? false : true,
                'title' => 'Wartennumernausgabe erfolgreich',
                'ticketprinter' => $ticketprinterHelper->getEntity(),
                'scopeId' => $scopeId,
                'organisation' => $ticketprinterHelper->getOrganisation(),
                'messages' => array($messages)
            )
        );
    }
}
