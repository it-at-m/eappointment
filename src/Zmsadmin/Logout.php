<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsclient\Auth;

/**
  * Handle requests concerning services
  *
  */
class Logout extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {

        session_destroy();
        setcookie(
            session_name(),
            null,
            time() - 42000,
            "/"
        );
        session_regenerate_id(true);

        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        \App::$http->readDeleteResult('/workstation/'. $workstation->useraccount['id'] .'/')->getEntity();

        return \BO\Slim\Render::redirect(
            
            'index',
            array(
                'title' => 'Anmeldung'
            ),
            array()
        );
    }
}
