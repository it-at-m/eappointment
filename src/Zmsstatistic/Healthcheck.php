<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;

/**
  * Handle requests concerning services
  *
  */
class Healthcheck extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $status = \App::$http->readGetResult('/status/')->getEntity();
        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        return \BO\Zmsclient\Status::testStatus($response, $status);
    }
}
