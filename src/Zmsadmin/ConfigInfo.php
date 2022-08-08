<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Config as Entity;

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
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();

        if ($request->isPost()) {
            $input = $request->getParsedBody();
            $entity = clone $config;
            $entity->setPreference($input['key'], $input['property'], $input['value']);
            $entity = \App::$http->readPostResult(
                '/config/',
                $entity
            )->getEntity();
            return \BO\Slim\Render::redirect(
                'configinfo', 
                array(
                    'title' => 'Konfiguration System',
                    'workstation' => $workstation,
                    'config' => $config,
                    'processExample' => $processExample,
                    'menuActive' => 'configinfo'
                ),
                array(
                    'success' => 'config_saved'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/configinfo.twig',
            array(
                'title' => 'Konfiguration System',
                'workstation' => $workstation,
                'config' => $config,
                'processExample' => $processExample,
                'menuActive' => 'configinfo',
                'success' => $success
            )
        );
    }
}
