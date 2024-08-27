<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesByServiceList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $serviceIds = explode(',', $request->getQueryParams()['serviceId'] ?? '');
        $serviceIds = array_unique($serviceIds);

        if (empty($serviceIds) || $serviceIds == ['']) {
            $responseContent = [
                'offices' => [],
                'error' => 'Invalid serviceId(s)',
            ];
            return $this->createJsonResponse($response, $responseContent, 400);
        }

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $providerList = $sources->getProviderList();
        $requestRelationList = $sources->getRequestRelationList();

        $offices = [];
        $notFoundIds = [];
        $addedOfficeIds = [];

        foreach ($serviceIds as $serviceId) {
            $found = false;
            foreach ($requestRelationList as $relation) {
                if ($relation->request->id == $serviceId) {
                    if (!in_array($relation->provider->id, $addedOfficeIds)) {
                        foreach ($providerList as $provider) {
                            if ($provider->id == $relation->provider->id) {
                                $offices[] = [
                                    "id" => $provider->id,
                                    "name" => $provider->name,
                                ];
                                $addedOfficeIds[] = $provider->id;
                                $found = true;
                                break;
                            }
                        }
                    } else {
                        $found = true;
                    }
                }
            }
            if (!$found) {
                $notFoundIds[] = $serviceId;
            }
        }

        if (empty($offices)) {
            $responseContent = [
                'offices' => [],
                'error' => 'Office(s) not found for the provided serviceId(s)',
            ];
            return $this->createJsonResponse($response, $responseContent, 404);
        }

        $responseContent = ['offices' => $offices];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following serviceId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        return $this->createJsonResponse($response, $responseContent, 200);
    }

    private function createJsonResponse(ResponseInterface $response, array $content, int $statusCode): ResponseInterface
    {
        $response = $response->withStatus($statusCode)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($content));
        return $response;
    }
}
