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
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }

        $changelogHelper = new ChangelogHelper();
        $changelogContent = $changelogHelper->getChangelogHtml();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/changelog.twig',
            array(
                'title' => 'Changelog',
                'menuActive' => 'changelog',
                'workstation' => $workstation,
                'changelogContent' => $changelogContent
            )
        );
    }
}
