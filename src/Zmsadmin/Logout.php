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
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
            \App::$http->readDeleteResult('/workstation/'. $workstation->useraccount['id'] .'/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ("BO\Zmsentities\Exception\UserAccountMissingLogin" !== $exception->template) {
                throw $exception;
            }
        }

        return \BO\Slim\Render::redirect(
            'index',
            array(
                'title' => 'Anmeldung'
            ),
            array()
        );
    }
}
