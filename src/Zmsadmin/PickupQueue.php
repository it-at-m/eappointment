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
        $processList = \App::$http->readGetResult('/workstation/process/pickup/', ['resolveReferences' => 1])
            ->getCollection();

        return \BO\Slim\Render::withHtml(
            $response,
            'block/pickup/table.twig',
            array(
              'processList' => $processList
            )
        );
    }
}
