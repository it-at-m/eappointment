<?php

/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmscalldisplay;

use BO\Slim\Render;
use BO\Zmsclient\Status;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handle requests concerning services
 */
class Healthcheck extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $response = Status::testStatus($response, function () {
            return \App::$http->readGetResult('/status/', ['includeProcessStats' => 0])->getEntity();
        });

        return Render::withLastModified($response, time(), '0');
    }
}
