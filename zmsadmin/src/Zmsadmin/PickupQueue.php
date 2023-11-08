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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $selectedScope = $validator->getParameter('selectedscope')->isNumber()->getValue();
        $limit = $validator->getParameter('limit')->isNumber()->setDefault(Pickup::$defaultLimit)->getValue();
        $offset = $validator->getParameter('offset')->isNumber()->setDefault(0)->getValue();
        $scopeId = ($selectedScope) ? $selectedScope : $workstation->scope['id'];
        $scope = \App::$http->readGetResult('/scope/'. $scopeId .'/')->getEntity();
        $department = \App::$http->readGetResult(
            '/scope/'. $workstation->scope['id'] .'/department/',
            [
                'resolveReferences' => 2
            ]
        )->getEntity();

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
              'limit' => $limit,
              'offset' => $offset,
              'processList' => static::getProcessList($scopeId, $limit, $offset),
              'processListNext' => static::getProcessList($scopeId, $limit, $offset + $limit),
            )
        );
    }

    public static function getProcessList($scopeId, $limit = 500, $offset = 0)
    {
        $processList = \App::$http->readGetResult('/workstation/process/pickup/', [
            'resolveReferences' => 1,
            'selectedScope' => $scopeId,
            'limit' => $limit,
            'offset' => $offset,
            'gql' => Helper\GraphDefaults::getPickup()
        ])->getCollection();
        return ($processList) ? $processList->sortPickupQueue() : $processList;
    }
}
