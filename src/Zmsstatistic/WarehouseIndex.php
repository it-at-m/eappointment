<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class WarehouseIndex extends BaseController
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
        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }
        $warehouse = \App::$http->readGetResult('/warehouse/')
          ->getEntity()
          ->toHashed()
          ->withRightsFromUseraccount($workstation->getUseraccount());

        return \BO\Slim\Render::withHtml(
            $response,
            'page/warehouseIndex.twig',
            array(
                'title' => 'Kategorien',
                'menuActive' => 'warehouse',
                'warehouse' => $warehouse,
                'workstation' => $workstation->getArrayCopy()
            )
        );
    }
}
