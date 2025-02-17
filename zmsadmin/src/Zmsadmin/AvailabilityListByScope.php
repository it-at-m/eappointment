<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsadmin\Exception\BadRequest as BadRequestException;
use BO\Zmsentities\Collection\AvailabilityList;

/**
 * Get all availabilities for a scope
 */
class AvailabilityListByScope extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return string
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        if (!array_key_exists('id', $args)) {
            throw new BadRequestException('Missing scope ID.');
        }
        $scopeId = Validator::value($args['id'])->isNumber()->getValue();
        $startDate = Validator::value($request->getQueryParams()['startDate'] ?? null)->isString()->getValue();
        $endDate = Validator::value($request->getQueryParams()['endDate'] ?? null)->isString()->getValue();

        try {
            $availabilityList = \App::$http
                ->readGetResult(
                    '/scope/' . $scopeId . '/availability/',
                    [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                        'resolveReferences' => 2
                    ]
                )
                ->getCollection()
                ->sortByCustomKey('startDate');

            $data = $availabilityList->getArrayCopy();
            if (!is_array($data) && is_object($data)) {
                $data = array_values((array)$data);
            }

            return \BO\Slim\Render::withJson($response, [
                'meta' => [],
                'data' => $data
            ]);
        } catch (\BO\Zmsclient\Exception $exception) {
            if ($exception->template == 'BO\Zmsapi\Exception\Availability\AvailabilityNotFound') {
                return \BO\Slim\Render::withJson($response, [
                    'meta' => [],
                    'data' => []
                ]);
            }
            throw $exception;
        }
    }
}
