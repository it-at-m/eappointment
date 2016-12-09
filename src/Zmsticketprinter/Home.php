<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

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
        $homeUrl = \BO\Zmsclient\Ticketprinter::getHomeUrl();

        if (! $homeUrl) {
            throw new Exception\HomeNotFound();
        }

        return $response->withRedirect($homeUrl);
    }
}
