<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;
use BO\Zmsentities\Scope;

class ScopeEmergency extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $entityId = Validator::value($args['id'])->isNumber()->getValue();
        $url = sprintf('/scope/%d/emergency/', $entityId);

        switch ($request->getMethod()) {
            case 'POST':
                $result = \App::$http->readPostResult($url, new Scope());
                break;
            case 'GET':
                $result = \App::$http->readDeleteResult($url);
                break;
        }
        return $result->getResponse();
    }
}
