<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Exception\UserAccountMissingLogin;

/**
 * Handle requests concerning services
 */
class Status extends BaseController
{
    private const array MISSING_LOGIN_TEMPLATES = [
        'BO\\Zmsentities\\Exception\\UserAccountMissingLogin',
        'BO\\Zmsbackend\\Workstation\\Exception\\WorkstationNotFound',
    ];

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if (in_array($exception->template, self::MISSING_LOGIN_TEMPLATES, true)) {
                throw new UserAccountMissingLogin();
            }
            throw $exception;
        }

        $result = \App::$http->readGetResult('/status/');
        return \BO\Slim\Render::withHtml(
            $response,
            'page/status.twig',
            array(
                'title' => 'Status der Terminvereinbarung',
                'status' => $result->getEntity(),
                'workstation' => $workstation,
                'isSuperuser' => $workstation->getUseraccount()->isSuperUser(),
            )
        );
    }
}
