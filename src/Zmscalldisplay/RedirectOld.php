<?php

/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

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
        $collections = Helper\EntryFromOldRoute::getFromOldRoute($request);

        return \BO\Slim\Render::redirect(
            'index',
            array(),
            $collections
        );
    }
}
