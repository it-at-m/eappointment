<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $sources = \App::$http->readGetResult('/source/'.\App::$source_name.'/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $providerProjectionList = [];
        foreach ($sources->getProviderList() as $provider) {
            $providerProjectionList[] = [
                "id" => $provider->id,
                "name" => $provider->displayName ?? $provider->name,
            ];
        }

        return Render::withJson($response, [
            "offices" => $providerProjectionList,
        ]);
    }
}
