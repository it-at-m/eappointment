<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Status as Query;
use BO\Zmsentities\Status;

class Healthcheck extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $response = \BO\Zmsclient\Status::testStatus($response, function (): Status {
            return (new Query())->readEntity(\App::$now, false);
        });
        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $response;
    }
}
