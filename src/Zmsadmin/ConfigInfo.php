<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class ConfigInfo extends BaseController
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
        $config = \App::$http->readGetResult('/config/')->getEntity();
        return \BO\Slim\Render::withHtml(
            $response,
            'page/configinfo.twig',
            array(
                'title' => 'Konfiguration System',
                'workstation' => $workstation,
                'config' => $config,
                'menuActive' => 'configinfo'
            )
        );
    }
}
