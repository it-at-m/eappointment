<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use \BO\Zmsclient\Auth;

class Logout extends BaseController
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
        \App::$http->readDeleteResult('/workstation/login/'. $this->workstation->useraccount['id'] .'/');
        return \BO\Slim\Render::redirect(
            'index',
            array(
                'title' => 'Anmeldung'
            ),
            array()
        );
    }
}
