<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Status as Query;

class Healthcheck extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $response = \BO\Zmsclient\Status::testStatus($response, function () {
            return (new Query())->readEntity(\App::$now, false);
        });
        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        return $response;
    }
}
