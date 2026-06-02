<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

class SourceIndex extends BaseController
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
        if (!$workstation->getUseraccount()->hasPermissions(['source'])) {
            throw new \BO\Zmsentities\Exception\UserAccountMissingRights();
        }
        $sourceList = \App::$http->readGetResult('/source/', ['resolveReferences' => 0])->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/sourceindex.twig',
            array(
                'title' => 'Mandanten',
                'menuActive' => 'source',
                'workstation' => $workstation,
                'sourceList' => $sourceList
            )
        );
    }
}
