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
        $validator = $request->getAttribute('validator');
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $selectedScope = $validator->getParameter('selectedscope')->isNumber()->getValue();
        $scopeId = ($selectedScope) ? $selectedScope : $workstation->scope['id'];
        $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/')->getEntity();
        $department = \App::$http->readGetResult(
            '/scope/'. $scopeId .'/department/',
            ['resolveReferences' => 2]
        )->getEntity();

        $processList = \App::$http->readGetResult('/workstation/process/pickup/', [
            'resolveReferences' => 1,
            'selectedScope' => $scopeId
        ])->getCollection();

        $validator = $request->getAttribute('validator');
        $handheld = $validator->getParameter('handheld')->isNumber()->setDefault(0)->getValue();
        $template = ($handheld) ? 'table-handheld' : 'table';

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/'. $template .'.twig',
            array(
              'workstation' => $workstation,
              'pickupList' => $department->getScopeList(),
              'department' => $department,
              'scope' => $scope,
              'processList' => ($processList) ? $processList->sortByName() : $processList,
            )
        );
    }
}
