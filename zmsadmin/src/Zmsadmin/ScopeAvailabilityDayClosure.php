<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsdb\Closure;
use BO\Zmsentities\Collection\AvailabilityList;
use Psr\Http\Message\ResponseInterface;

class ScopeAvailabilityDayClosure extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $scopeId = $args['id'];
        $date = $args['date'];

        try {
            $closureToggled = \App::$http->readPostResult(
                '/scope/' . $scopeId . '/availability/' . $date . '/closure/toggle/',
                new \BO\Zmsentities\Closure()
            )->getEntity();
        } catch (\Exception $e) {
            var_dump($e->getMessage());exit;
        }

        return \BO\Slim\Render::withJson(
            $response,
            [
                'closure' => $closureToggled
            ]
        );
    }
}
