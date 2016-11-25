<?php

/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

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
        $buttonList = Helper\EntryFromOldRoute::getFromOldMehrfachKiosk($request);
        return \BO\Slim\Render::redirect(
            'Index',
            array (),
            array (
                'ticketprinter' => array (
                    'buttonlist' => $buttonList
                )
            )
        );
    }
}
