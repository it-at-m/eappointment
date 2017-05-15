<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Config as Query;
use \BO\Zmsapi\Helper\User;

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
        try {
            (new Helper\User($request))->checkRights('basic');
        } catch (\Exception $exception) {
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
