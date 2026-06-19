<?php

namespace BO\Zmsadmin\Helper;

use BO\Slim\Render;
use BO\Zmsadmin\BaseController;
use BO\Zmsadmin\Exception\BadRequest;
use BO\Zmsentities\Collection\AvailabilityList;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AvailabilityCheckDayOff extends BaseController
{
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();

        if (!isset($input['availabilityList']) || !is_array($input['availabilityList'])) {
            throw new BadRequest();
        }

        $collection = (new AvailabilityList())->addData($input['availabilityList']);

        return Render::withJson($response, [
            'overridesDayOff' => $collection->hasDayOffOverride()
        ]);
    }
}
