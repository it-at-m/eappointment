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
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $ownerList = \App::$http->readGetResult('/owner/', array('resolveReferences' => 4))->getCollection();
        $success = $request->getAttribute('validator')->getParameter('success')->isString()->getValue();
        return \BO\Slim\Render::withHtml(
            $response,
            'page/ownerOverview.twig',
            array(
                'title' => 'Behörden und Standorte',
                'menuActive' => 'owner',
                'workstation' => $workstation,
                'ownerList' => $ownerList,
                'success' => $success
            )
        );
    }
}
