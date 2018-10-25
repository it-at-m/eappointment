<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin;

class SourceEdit extends BaseController
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
        if (!$workstation->hasSuperUseraccount()) {
            throw new Exception\NotAllowed();
        }
        $source = \App::$http->readGetResult('/source/'. $args['name'] .'/', ['resolveReferences' => 2])->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/sourceedit.twig',
            array(
                'title' => 'Mandanten bearbeiten',
                'menuActive' => 'source',
                'workstation' => $workstation,
                'source' => $source
            )
        );
    }
}
