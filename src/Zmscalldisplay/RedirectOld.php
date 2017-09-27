<?php

/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

class RedirectOld
{

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * add params to session if valid and redirect to calendar
     *
     * @return string
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
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
