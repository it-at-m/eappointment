<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsstatistic;

use BO\Slim\Render;
use BO\Zmsentities\Exception\UserAccountMissingLogin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handle requests concerning services
 */
class Status extends BaseController
{
    protected $withAccess = false;

    private const array MISSING_LOGIN_TEMPLATES = [
        'BO\\Zmsentities\\Exception\\UserAccountMissingLogin',
        'BO\\Zmsbackend\\Workstation\\Exception\\WorkstationNotFound',
    ];

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    #[\Override]
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\BO\Zmsclient\Exception $exception) {
            if (in_array($exception->template, self::MISSING_LOGIN_TEMPLATES, true)) {
                throw new UserAccountMissingLogin();
            }
            throw $exception;
        }

        $result = \App::$http->readGetResult('/status/');
        return Render::withHtml(
            $response,
            'page/status.twig',
            array(
                'title' => 'Status der Terminvereinbarung',
                'workstation' => $workstation,
                'status' => $result->getEntity(),
                'isSuperuser' => $workstation->getUseraccount()->isSuperUser(),
            )
        );
    }
}
