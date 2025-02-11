<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin\Helper;

use BO\Zmsadmin\BaseController;
use BO\Zmsentities\Availability as Entity;
use BO\Zmsentities\Collection\AvailabilityList as Collection;
use BO\Zmsentities\Collection\ProcessList;

class AvailabilityCalcSlots extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $collection = (new Collection())->addData($input['availabilityList']);

        $data['maxWorkstationCount'] = $collection->getMaxWorkstationCount();
        $data['maxSlots'] = $collection->getSummerizedSlotCount();
        $data['busySlots'] = $input['busySlots'];

        return \BO\Slim\Render::withJson(
            $response,
            $data
        );
    }
}
