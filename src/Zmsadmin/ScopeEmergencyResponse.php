<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

/**
 * Handle requests concerning services
 *
 */
class ScopeEmergencyResponse extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $url = sprintf('/scope/%d/emergency/respond/', $entityId);
        $result = \App::$http->readPostResult($url);
        return $result->getResponse();
    }
}
