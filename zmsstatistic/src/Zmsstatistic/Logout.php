<?php

/**
 * @package Zmsstatistic
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

use BO\Zmsclient\Auth;

class Logout extends BaseController
{
    protected $resolveLevel = 0;
    protected $withAccess = false;

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
