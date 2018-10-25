<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class Reset extends BaseController
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
        \BO\Zmsclient\Ticketprinter::setHash("", $request);

        return \BO\Slim\Render::withHtml(
            $response,
            'page/reset.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Standort neusetzen'
            )
        );
    }
}
