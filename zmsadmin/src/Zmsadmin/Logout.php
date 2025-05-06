<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsclient\Auth;
use App;

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
            \App::$http->readDeleteResult('/workstation/login/' . $workstation->useraccount['id'] . '/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if ("BO\Zmsentities\Exception\UseraccountMissingLogin" !== $exception->template) {
                throw $exception;
            }
        }
        $sessionHash = hash('sha256', \BO\Zmsclient\Auth::getKey());
        App::$log->info('User logged out', [
            'event' => 'auth_logout',
            'timestamp' => date('c'),
            'username' => $workstation->useraccount['id'],
            'hashed_session_token' => $sessionHash,
            'logout_type' => 'manual',
            'application' => 'zmsadmin'
        ]);
        \BO\Zmsclient\Auth::removeKey();
        return \BO\Slim\Render::withHtml(
            $response,
            'page/logout.twig',
            array(
                'title' => 'Erfolgreich abgemeldet'
            )
        );
    }
}
