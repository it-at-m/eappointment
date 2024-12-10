<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class WarehouseIndex extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $warehouse = \App::$http->readGetResult('/warehouse/')
          ->getEntity()
          ->toHashed();

        return Render::withHtml(
            $response,
            'page/warehouseIndex.twig',
            array(
                'title' => 'Kategorie auswählen',
                'menuActive' => 'warehouse',
                'warehouse' => $warehouse,
                'workstation' => $this->workstation->getArrayCopy()
            )
        );
    }
}
