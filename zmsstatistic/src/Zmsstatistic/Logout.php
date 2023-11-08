<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Logout extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        \App::$http->readDeleteResult('/workstation/login/'. $this->workstation->useraccount['id'] .'/');
        return Render::redirect(
            'index',
            array(
                'title' => 'Anmeldung'
            ),
            array()
        );
    }
}
