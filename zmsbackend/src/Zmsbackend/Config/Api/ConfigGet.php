<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Config\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Config\Service\Config as Query;
use BO\Zmsbackend\Helper\User;

class ConfigGet extends \BO\Zmsbackend\Api\BaseController
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
        try {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        } catch (\Exception $exception) {
            $token = $request->getHeader('X-Token');
            if (\App::SECURE_TOKEN != current($token)) {
                throw new \BO\Zmsbackend\Config\Exception\ConfigAuthentificationFailed();
            }
        }

        $config = (new Query())->readEntity();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $config;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
