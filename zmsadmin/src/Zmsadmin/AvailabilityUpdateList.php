<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Zmsentities\Availability;
use BO\Zmsentities\Collection\AvailabilityList;
use BO\Slim\Render;

/**
 * Update availabilites, API proxy
 *
 */
class AvailabilityUpdateList extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $collection = new AvailabilityList($input);
        $availabilityList = \App::$http->readPostResult('/availability/', $collection)->getCollection();
        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $availabilityList);
    }
}
