<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Pickup extends BaseController
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
        $processList = \App::$http->readGetResult('/workstation/process/pickup/', ['resolveReferences' => 1])
            ->getCollection();
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/pickup.twig',
            array(
              'title' => 'Abholer verwalten',
              'menuActive' => 'pickup',
              'workstation' => $workstation->getArrayCopy(),
              'department' => $department,
              'source' => $workstation->getRedirect(),
              'cluster' => (new Helper\ClusterHelper($workstation))->getEntity(),
              'processList' => $processList
            )
        );
    }
}
