<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Status as Query;

/**
  * Handle requests concerning services
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
        $status = (new Query())->readEntity();
        $response = \BO\Slim\Render::withLastModified($response, time(), '0');
        return \BO\Zmsclient\Status::testStatus($response, $status);
    }
}
