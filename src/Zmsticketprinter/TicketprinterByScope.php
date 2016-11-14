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

/**
 * Handle requests concerning services
 */
class TicketprinterByScope extends BaseController
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
        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $organisation = \App::$http->readGetResult('/organisation/scope/'. $scopeId . '/')->getEntity();
        $ticketprinter = \BO\Zmsclient\Ticketprinter::getHash();
        if (!$ticketprinter) {
            $ticketprinter = \App::$http->readGetResult('/organisation/'. $organisation->id . '/hash/')->getEntity();
        }
        $ticketprinter->buttonlist = 's'. $scopeId;
        $ticketprinter = \App::$http->readPostResult('/ticketprinter/', $ticketprinter)->getEntity();

        var_dump($ticketprinter);

        return \BO\Slim\Render::withHtml(
            $response,
            'page/buttonSingleRow_default.twig',
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
            )
        );
    }
}
