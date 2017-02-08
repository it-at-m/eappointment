<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;
use \BO\Mellon\Validator;

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
                'ticketprinter' => $ticketprinter,
                'scopeId' => $scopeId,
                'organisation' => \App::$http->readGetResult(
                    '/scope/'. $scopeId . '/organisation/',
                    ['resolveReferences' => 2]
                )->getEntity(),
                'messages' => array($messages)
            )
        );
    }
}
