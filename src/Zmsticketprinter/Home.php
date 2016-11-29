<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use \BO\Zmsentities\Ticketprinter as Entity;

class Home extends BaseController
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
        $homeUrl = $validator->getParameter('home')->isUrl()->getValue();

        if ($homeUrl) {
            \BO\Zmsclient\Ticketprinter::setHomeUrl($homeUrl);
        } else {
            $homeUrl = \BO\Zmsclient\Ticketprinter::getHomeUrl();
        }

        if (! $homeUrl) {
            throw new Exception('No Home URL found');
        }

        return $response->withRedirect($homeUrl);
    }
}
