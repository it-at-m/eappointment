<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class TicketprinterProcess extends BaseController
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
        $validate = $validator->getParameter('hasWaitingnumber')->isBool()->getValue();
        $ticketprinter = (new Helper\Ticketprinter($args, $request))->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/buttonSingleRow_default.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'validate' => $validate
            )
        );
    }
}
