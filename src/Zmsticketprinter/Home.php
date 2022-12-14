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
        $homeUrl = Ticketprinter::getHomeUrl();
        if (! $homeUrl) {
            throw new Exception\HomeNotFound();
        }

        return Render::withLastModified($response, time(), '0')->withRedirect($homeUrl);
    }
}
