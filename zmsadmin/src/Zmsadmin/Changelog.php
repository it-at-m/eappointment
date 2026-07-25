<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsadmin\Helper\ChangelogHelper;

class Changelog extends BaseController
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
        try {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }

        $changelogHelper = new ChangelogHelper();
        $changelogContent = $changelogHelper->getChangelogHtml();
        //TODO: Check if safe when refactoring to vue.js in the future
        $config = \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/changelog.twig',
            array(
                'title' => 'Changelog',
                'menuActive' => 'changelog',
                'workstation' => $workstation,
                'changelogContent' => $changelogContent,
                'config' => $config
            )
        );
    }
}
