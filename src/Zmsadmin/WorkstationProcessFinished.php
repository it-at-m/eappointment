<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class WorkstationProcessFinished extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $department = \App::$http
            ->readGetResult('/scope/'. $workstation->scope['id'] .'/department/', ['resolveReferences' => 2])
            ->getEntity();
        $requestList = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/request/')->getCollection();
        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstationProcessFinished.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'pickupList' => $department->getScopeList(),
                'requestList' => $requestList->toSortedByGroup(),
                'menuActive' => 'workstation'
            )
        );
    }
}
