<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

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
        try {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 0])->getEntity();
            \App::$http->readDeleteResult('/workstation/login/'. $workstation->useraccount['id'] .'/')->getEntity();
            error_log("____________________________________removeKey___________________________________: ");
            if(\App::ZMS_AUTHORIZATION_TYPE === "Keycloak"){ 
                try {
                    $oAuth = new OAuth($request);
                    $oAuth->logout();
                } catch (\Exception $workstationexception) {
                    error_log("____________________________________VVVVVVVVVV___________________________________: ". $workstationexception);
                }
                return \BO\Slim\Render::redirect('workstationSelect', array(), array());
            }
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
