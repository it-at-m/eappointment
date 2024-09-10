<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ServicesByOfficeList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $officeIds = explode(',', $request->getQueryParams()['officeId'] ?? '');
        $officeIds = array_unique($officeIds);

        if (empty($officeIds) || $officeIds == ['']) {
            $responseContent = [
                'services' => [],
                'error' => 'Invalid officeId(s)',
            ];
            $response = $response->withStatus(400)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        }

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();
        $requestList = $sources->getRequestList();
        $requestRelationList = $sources->getRequestRelationList();

        $services = [];
        $notFoundIds = [];
        $addedServices = [];

        foreach ($officeIds as $officeId) {
            $found = false;
            foreach ($requestRelationList as $relation) {
                if ($relation->provider->id == $officeId) {
                    foreach ($requestList as $request) {
                        if ($request->id == $relation->request->id && !in_array($request->id, $addedServices)) {
                            $services[] = [
                                "id" => $request->id,
                                "name" => $request->name,
                                "maxQuantity" => $request->getAdditionalData()['maxQuantity'] ?? 1,
                            ];
                            $addedServices[] = $request->id;
                            $found = true;
                        }
                    }
                }
            }
            if (!$found) {
                $notFoundIds[] = $officeId;
            }
        }

        if (empty($services)) {
            $responseContent = [
                'services' => [],
                'error' => 'Service(s) not found for the provided officeId(s)',
            ];
            $response = $response->withStatus(404)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        }

        $responseContent = ['services' => $services];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following officeId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        $response = $response->withStatus(200)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($responseContent));
        return $response;
    }
}
