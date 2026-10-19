<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use Psr\Http\Message\ResponseInterface;

class ScopeAvailabilityDayClosure extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $scopeId = $args['id'];
        $date = $args['date'];

        $closureToggled = \App::$http->readPostResult(
            '/scope/' . $scopeId . '/availability/' . $date . '/closure/toggle/',
            new \BO\Zmsentities\Closure()
        )->getEntity();

        // Plain boolean so the day-view banner can update without relying on
        // schema serialization of the dynamic `existing` property.
        return \BO\Slim\Render::withJson(
            $response,
            [
                'closure' => [
                    'existing' => (bool) ($closureToggled['existing'] ?? false),
                ],
            ]
        );
    }
}
