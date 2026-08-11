<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Status\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Status\Exception\StatusAuthenticationFailed;
use BO\Zmsbackend\Status\Service\Status;
use BO\Zmsentities\Exception\UserAccountMissingLogin;
use BO\Zmsentities\Exception\UserAccountMissingRights;

class StatusGet extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $this->assertStatusAccess($request);

        $validator = $request->getAttribute('validator');
        $includeProcessStats = $validator->getParameter('includeProcessStats')->isNumber()->setDefault(1)->getValue();
        $status = (new \BO\Zmsbackend\Status\Service\Status())->readEntity(\App::$now, $includeProcessStats);
        $status['version'] = \BO\Zmsbackend\Helper\Version::getArray();
        if (\App::DEBUG) {
            $status['opcache'] = [
                'config' => opcache_get_configuration(),
                'status' => opcache_get_status(false)
            ];
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $status;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    /**
     * Allow access with a logged-in workstation (admin UI) or X-Token matching
     * ZMS_CONFIG_SECURE_TOKEN (Grafana / status-logger). Same idea as ConfigGet,
     * with strict token comparison and a narrow auth-exception catch.
     */
    private function assertStatusAccess(\Psr\Http\Message\RequestInterface $request): void
    {
        try {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
            return;
        } catch (UserAccountMissingLogin | UserAccountMissingRights $exception) {
            // Fall through to X-Token for scrapers / unauthenticated callers.
        }

        $token = $request->getHeaderLine('X-Token');
        if ($token === '' || !hash_equals((string) \App::SECURE_TOKEN, $token)) {
            throw new StatusAuthenticationFailed();
        }
    }
}
