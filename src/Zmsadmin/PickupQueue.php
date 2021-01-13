<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class PickupQueue extends BaseController
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
        $department = \App::$http->readGetResult('/scope/'. $workstation->scope['id'] .'/department/')->getEntity();

        //$clusterHelper = new Helper\ClusterHelper($workstation);
        //$cluster = ($clusterHelper::isClusterEnabled()) ? $clusterHelper->getEntity() : null;
        $processList = \App::$http->readGetResult('/workstation/process/pickup/', ['resolveReferences' => 1])
            ->getCollection();

        $validator = $request->getAttribute('validator');
        $handheld = $validator->getParameter('handheld')->isNumber()->setDefault(0)->getValue();
        $template = ($handheld) ? 'table-handheld' : 'table';

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/'. $template .'.twig',
            array(
              'workstation' => $workstation,
              'department' => $department,
              'processList' => ($processList) ? $processList->sortByAppointmentDate() : $processList,
              //'cluster' => $cluster
            )
        );
    }
}
