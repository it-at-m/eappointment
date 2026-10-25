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
        $changelogContent = $this->withoutReleasesBeforeMarch2023($changelogHelper->getChangelogHtml());
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

    /**
     * Releases numbered 2.25.00 and earlier, and notes from before 15 March 2023,
     * stay in the published changelog file. They are not shown on this page.
     */
    private function withoutReleasesBeforeMarch2023(string $html): string
    {
        $cut = strlen($html);
        foreach (array('2.24.', 'INT_MS_') as $marker) {
            $position = strpos($html, '>' . $marker);
            if ($position === false) {
                continue;
            }
            $heading = strrpos(substr($html, 0, $position), '<h2');
            if ($heading !== false && $heading < $cut) {
                $cut = $heading;
            }
        }
        return substr($html, 0, $cut);
    }
}
