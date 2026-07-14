<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Status\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Status\Service\Status as Query;
use BO\Zmsentities\Status;

class Healthcheck extends \BO\Zmsbackend\Api\BaseController
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
