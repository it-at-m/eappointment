<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Exception\UserAccountMissingRights;

class Dayoff extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['dayoff'])) {
            throw new UserAccountMissingRights();
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'page/dayoff.twig',
            array(
                'title' => 'Allgemein gültige Feiertage - Jahresauswahl',
                'workstation' => $workstation,
                'menuActive' => 'dayoff'
            )
        );
    }
}
