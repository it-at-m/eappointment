<?php

namespace BO\Zmsadmin\Helper;

use BO\Zmsadmin\BaseController;
use BO\Zmsadmin\Exception\BadRequest;
use BO\Zmsentities\Collection\AvailabilityList;

class AvailabilityCheckDayOff extends BaseController
{
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();

        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequest();
        }

        $collection = (new AvailabilityList())->addData($input['availabilityList']);

        return \BO\Slim\Render::withJson($response, [
            'overridesDayOff' => $collection->hasDayOffOverride()
        ]);
    }
}
