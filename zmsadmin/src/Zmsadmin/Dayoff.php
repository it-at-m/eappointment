<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Dayoff extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        if (!$workstation->getUseraccount()->hasPermissions(['dayoff'])) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingRights();
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
