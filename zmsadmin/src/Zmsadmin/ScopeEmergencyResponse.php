<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

class ScopeEmergencyResponse extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $url = sprintf('/scope/%d/emergency/respond/', $entityId);
        $result = \App::$http->readPostResult($url, new \BO\Zmsentities\Scope());
        return $result->getResponse();
    }
}
