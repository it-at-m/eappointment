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
        $warehouse = \App::$http->readGetResult('/warehouse/')
          ->getEntity()
          ->toHashed();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/warehouseIndex.twig',
            array(
                'title' => 'Kategorien',
                'menuActive' => 'warehouse',
                'warehouse' => $warehouse,
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
