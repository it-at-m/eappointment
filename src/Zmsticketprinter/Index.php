<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class Index extends BaseController
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

        if (1 == count($ticketprinter->buttons) && 'scope' == $ticketprinter->buttons[0]['type']) {
             return \BO\Slim\Render::redirect(
                 'TicketprinterByScope',
                 array (
                     'scopeId' => $ticketprinter->buttons[0]['scope']['id']
                 ),
                 array ()
             );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/buttonMultiRow_default.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'validate' => $validate,
                'wrapper' => (2 == count($ticketprinter->buttons)) ? 'button_multirow_deep' : 'button_multirow'
            )
        );
    }
}
