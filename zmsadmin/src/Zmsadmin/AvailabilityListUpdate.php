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
class AvailabilityListUpdate extends BaseController
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

        try {
            $apiResponse = \App::$http->readPostResult('/availability/', $collection);
            $availabilityList = $apiResponse->getCollection();
            $statusCode = $apiResponse->getResponse()->getStatusCode();

            $response = Render::withLastModified($response, time(), '0');
            return Render::withJson($response, $availabilityList, $statusCode);
        } catch (\Exception $e) {
            // If there's an API error, preserve its status code and message
            $errorResponse = $e->getResponse();
            $statusCode = $errorResponse ? $errorResponse->getStatusCode() : 500;
            $errorData = $errorResponse ? json_decode($errorResponse->getBody(), true) : ['error' => $e->getMessage()];

            return Render::withJson($response, $errorData, $statusCode);
        }
    }
}
