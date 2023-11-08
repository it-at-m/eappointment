<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Healthcheck extends BaseController
{
    protected $withAccess = false;
    
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $response = \BO\Zmsclient\Status::testStatus($response, function () {
            return \App::$http->readGetResult('/status/', ['includeProcessStats' => 0])->getEntity();
        });

        return Render::withLastModified($response, time(), '0');
    }
}
