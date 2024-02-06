<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

use BO\Slim\Render;
use BO\Zmsclient\Ticketprinter as TicketprinterClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Home extends BaseController
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
        $homeUrl = static::getHomeUrl($request);
        if (! $homeUrl) {
            throw new Exception\HomeNotFound();
        }

        return Render::withLastModified($response, time(), '0')->withRedirect($homeUrl, 301);
    }

    public function getHomeUrl(RequestInterface $request): string
    {
        $cookies = $request->getCookieParams();
        $homeUrl = TicketprinterClient::getHomeUrl();
        if (array_key_exists(TicketprinterClient::HOME_URL_COOKIE_NAME, $cookies) && ! $homeUrl) {
            $homeUrl = $cookies[TicketprinterClient::HOME_URL_COOKIE_NAME];
        }
        return $homeUrl;
    }
}
