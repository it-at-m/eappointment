<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Session;
use BO\Mellon\Validator;

class SessionGet extends BaseController
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
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $session = (new Session())->readEntity($args['name'], $args['id']);
        if (! $session) {
            throw new Exception\Session\SessionNotFound();
        }
        $session->id = $args['id'];
        $session->name = $args['name'];
        $session->testValid();

        $message = Response\Message::create($request);
        $message->data = $session;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
