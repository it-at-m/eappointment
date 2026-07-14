<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Session\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Session\Service\Session;

class SessionDelete extends \BO\Zmsbackend\Api\BaseController
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
        $session = (new \BO\Zmsbackend\Session\Service\Session())->readEntity($args['name'], $args['id']);
        if (! $session || ! (new \BO\Zmsbackend\Session\Service\Session())->deleteEntity($args['name'], $args['id'])) {
            throw new \BO\Zmsbackend\Session\Exception\SessionDeleteFailed();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $session;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
