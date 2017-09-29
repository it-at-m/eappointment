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
        $processExample = ((new \BO\Zmsentities\Process)->getExample());
        $processExample->scope = ((new \BO\Zmsentities\Scope)->getExample());
        $processExample->requests[] = (new \BO\Zmsentities\Request())->getExample();
        return \BO\Slim\Render::withHtml(
            $response,
            'page/configinfo.twig',
            array(
                'title' => 'Konfiguration System',
                'workstation' => $workstation,
                'config' => $config,
                'processExample' => $processExample,
                'menuActive' => 'configinfo'
            )
        );
    }
}
