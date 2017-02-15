<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class OwnerOverview extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function invokeHook(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences'=>4))->getCollection();
        $organisationList = $ownerList->getOrganisationsByOwnerId(23);

        if (!count($ownerList)) {
            return \BO\Slim\Render::withHtml($response, 'page/404.twig', array());
        }

        return \BO\Slim\Render::withHtml(

            $response,
            'page/ownerOverview.twig',
            array(
                'title' => 'Behörden und Standorte',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'owner' => current($ownerList),
                'itemList' => $organisationList->sortByName(),
            )
        );
    }
}
