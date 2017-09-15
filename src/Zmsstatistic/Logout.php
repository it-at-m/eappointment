<?php
/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

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
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 0])->getEntity();
            \App::$http->readDeleteResult('/workstation/login/'. $workstation->useraccount['id'] .'/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ("BO\Zmsentities\Exception\UseraccountMissingLogin" !== $exception->template) {
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
