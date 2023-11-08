<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Availability as Entity;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Slim\Render;

/**
 * Update availabilites, API proxy
 *
 */
class AvailabilityUpdateSingle extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $validator = $request->getAttribute('validator');
        $input = $validator->getInput()->isJson()->assertValid()->getValue();
        $entity = new Entity($input);
        $availability = \App::$http->readPostResult('/availability/'. $args['id'] .'/', $entity)->getEntity();
        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $availability);
    }
}
