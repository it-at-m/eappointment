<?php
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use BO\Zmsticketprinter\Home;
use BO\Zmsticketprinter\Helper\Ticketprinter as Helper;

/**
 * Handle requests concerning services
 */
class Status extends BaseController
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
        $status['homeurl'] = Home::getHomeUrl($request);
        $status['hash'] = Helper::getHashFromRequest($request);
        if ($status['hash']) {
            $status['ticketprinter'] = \App::$http
                ->readGetResult('/ticketprinter/'. $status['hash'] . '/')
                ->getEntity();
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/status.twig',
            array(
                'title' => 'Status des Ticketprinter',
                'status' => $status,
                'cookies' => $request->getCookieParams()
            )
        );
    }
}
