<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Config as Query;
use BO\Zmsapi\Helper\User as UserHelper;

class ConfigGet extends BaseController
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
        // Guard 1: logged-in user with config permission or superuser
        // Guard 2: legacy secure token (used e.g. from zmsadmin index before login)
        try {
            UserHelper::$request = $request;
            $workstation = UserHelper::readWorkstation(1);
            $userAccount = $workstation->getUseraccount();

            // If we have a logged-in user, enforce permission-based access
            if ($userAccount->hasId()) {
                if (!$userAccount->isSuperUser() && !$userAccount->hasPermissions(['config'])) {
                    throw new \BO\Zmsentities\Exception\UserAccountMissingPermissions('Missing config permission');
                }
            } else {
                // No logged-in user: fall through to token-based auth below
                throw new \RuntimeException('No logged-in user');
            }
        } catch (\Exception $exception) {
            // Fallback for callers that use the secure token instead of a user session
            $token = $request->getHeader('X-Token');
            if (\App::SECURE_TOKEN != current($token)) {
                throw new Exception\Config\ConfigAuthentificationFailed();
            }
        }

        $config = (new Query())->readEntity();

        $message = Response\Message::create($request);
        $message->data = $config;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
