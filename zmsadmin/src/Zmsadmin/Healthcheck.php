<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsclient\Status;
use BO\Zmsentities\Schema\Entity;


/**
  * Handle requests concerning services
  *
  */
class Healthcheck extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    #[\Override]
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $response = Status::testStatus($response, function (): Entity|false|null {
            return \App::$http->readGetResult('/status/', ['includeProcessStats' => 0])->getEntity();
        });
        $response = Render::withLastModified($response, time(), '0');
        return $response;
    }
}
