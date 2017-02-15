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
class ScopeEmergency extends BaseController
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

        $url = sprintf('/scope/%d/emergency/', $entityId);

        switch ($request->getMethod()) {
            case 'POST':
                $result = \App::$http->readPostResult($url);
                return $result->getResponse();
            case 'DELETE':
                $result = \App::$http->readDeleteResult($url);
                return $result->getResponse();
        }
    }
}
