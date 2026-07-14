<?php

namespace BO\Zmsbackend\Request\Api;

use BO\Slim\Render;

class RequestVariantList extends \BO\Zmsbackend\Api\BaseController
{
    #[\Override]
    public function readResponse($request, $response, array $args)
    {
        $list = (new \BO\Zmsbackend\RequestVariant\Service\RequestVariant())->readAll();

        $msg = \BO\Zmsbackend\Api\Response\Message::create($request);
        $msg->data = $list;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $msg->setUpdatedMetaData(), $msg->getStatuscode());
    }
}
