<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServicesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestList = $sources->getRequestList() ?? [];
        $servicesProjectionList = [];

        foreach ($requestList as $request) {
            $additionalData = $request->getAdditionalData();
            $servicesProjectionList[] = [
                "id" => $request->getId(),
                "name" => $request->getName(),
                "maxQuantity" => $additionalData['maxQuantity'] ?? 1
            ];
        }

        return Render::withJson($response, [
            "services" => $servicesProjectionList,
        ]);
    }
}
