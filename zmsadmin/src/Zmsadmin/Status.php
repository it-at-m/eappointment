<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsadmin;

use BO\Zmsentities\Exception\UserAccountMissingLogin;
use BO\Zmsentities\Exception\UserAccountMissingRights;

/**
 * Handle requests concerning services
 */
class Status extends BaseController
{
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
        } catch (\Exception $workstationexception) {
            throw new UserAccountMissingLogin();
        }

        if (!$workstation->getUseraccount()->hasPermissions(['superuser'])) {
            throw new UserAccountMissingRights();
        }

        $result = \App::$http->readGetResult('/status/');
        return \BO\Slim\Render::withHtml(
            $response,
            'page/status.twig',
            array(
                'title' => 'Status der Terminvereinbarung',
                'status' => $result->getEntity(),
                'workstation' => $workstation
            )
        );
    }
}
