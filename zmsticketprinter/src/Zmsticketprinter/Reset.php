<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use BO\Slim\Render;
use BO\Zmsclient\Ticketprinter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Reset extends BaseController
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
        Ticketprinter::setHash("", $request);

        return Render::withHtml(
            $response,
            'page/reset.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Standort neusetzen'
            )
        );
    }
}
