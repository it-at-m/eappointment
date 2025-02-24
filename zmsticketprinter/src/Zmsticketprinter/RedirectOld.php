<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsticketprinter;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class RedirectOld extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * add params to session if valid and redirect to calendar
     *
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $buttonList = Helper\EntryFromOldRoute::getFromOldMehrfachKiosk($request);

        return Render::redirect(
            'Index',
            array(),
            array(
                'ticketprinter' => array(
                    'buttonlist' => $buttonList
                )
            )
        );
    }
}
