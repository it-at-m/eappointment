<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServicesByOfficeList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $officeId = $request->getQueryParams()['officeId'] ?? null;

        if (is_null($officeId)) {
            return $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(['error' => 'Invalid or missing officeId']));
        }

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $requestRelationList = $sources->getRequestRelationList();
        $serviceIds = [];

        foreach ($requestRelationList as $relation) {
            if ($relation->provider->id == $officeId) {
                $serviceIds[] = $relation->request->id;
            }
        }

        $services = [];
        foreach ($sources->getRequestList() as $request) {
            if (in_array($request->id, $serviceIds)) {
                $services[] = [
                    'id' => $request->id,
                    'name' => $request->name,
                    'description' => $request->description ?? ''
                ];
            }
        }

        if (empty($services)) {
            return $response->withStatus(404)
                ->withHeader('Content-Type', 'application/json')
                ->getBody()->write(json_encode(['error' => 'No services found for the provided officeId']));
        }

        return Render::withJson($response, ['services' => $services]);
    }
}
