<?php

namespace BO\Zmsapi;

use BO\Slim\Render;

class RequestVariantList extends BaseController
{
    public function readResponse($request, $response, array $args)
    {
        $list = (new \BO\Zmsdb\RequestVariant())->readAll();

        $msg = Response\Message::create($request);
        $msg->data = $list;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $msg->setUpdatedMetaData(), $msg->getStatuscode());
    }
}
