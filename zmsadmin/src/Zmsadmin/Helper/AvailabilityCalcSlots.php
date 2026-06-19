<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin\Helper;

use BO\Slim\Render;
use BO\Zmsadmin\BaseController;
use BO\Zmsentities\Collection\AvailabilityList;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class AvailabilityCalcSlots extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $collection = (new AvailabilityList())->addData($input['availabilityList']);

        $data['maxWorkstationCount'] = $collection->getMaxWorkstationCount();
        $data['maxSlots'] = $collection->getSummerizedSlotCount();
        $data['busySlots'] = $input['busySlots'];

        return Render::withJson(
            $response,
            $data
        );
    }
}
