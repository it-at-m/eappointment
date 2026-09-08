<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Profile extends BaseController
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
        $profile = \App::$http->readGetResult('/workstation/profile/')->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/profile.twig',
            [
                'title' => 'Nutzerprofil',
                'menuActive' => 'profile',
                'workstation' => $workstation,
                'profile' => $profile,
            ]
        );
    }
}
