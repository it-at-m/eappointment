<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use App;
use BO\Slim\Render;
use BO\Zmsclient\Auth;
use BO\Zmsclient\Exception;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;


class Logout extends BaseController
{
    protected $resolveLevel = 0;
    protected $withAccess = false;

    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        try {
            $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 0])->getEntity();
            \App::$http->readDeleteResult('/workstation/login/' . $workstation->useraccount['id'] . '/')->getEntity();
        } catch (Exception $exception) {
            if ("BO\Zmsentities\Exception\UseraccountMissingLogin" !== $exception->template) {
                throw $exception;
            }
        }
        $sessionHash = hash('sha256', Auth::getKey());
        App::$log->info('User logged out', [
            'event' => 'auth_logout',
            'timestamp' => date('c'),
            'username' => $workstation->useraccount['id'],
            'hashed_session_token' => $sessionHash,
            'logout_type' => 'manual',
            'application' => 'zmsstatistic'
        ]);
        Auth::removeKey();
        return Render::withHtml(
            $response,
            'page/logout.twig',
            array(
                'title' => 'Erfolgreich abgemeldet'
            )
        );
    }
}
