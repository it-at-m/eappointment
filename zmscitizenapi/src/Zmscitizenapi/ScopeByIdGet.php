<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopeByIdGet extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $scopeIds = explode(',', $request->getQueryParams()['scopeId'] ?? '');
        $scopeIds = array_unique($scopeIds);

        if (empty($scopeIds) || $scopeIds == ['']) {
            $responseContent = [
                'scopes' => [],
                'error' => 'Invalid scopeId(s)',
            ];
            $response = $response->withStatus(400)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        }

        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();
        $scopeList = $sources->getScopeList();

        $scopes = [];
        $notFoundIds = [];

        foreach ($scopeIds as $scopeId) {
            $found = false;
            foreach ($scopeList as $scopeItem) {
                if ($scopeItem->id == $scopeId) {
                    $scopes[] = [
                        "id" => $scopeItem->id,
                        "provider" => [
                            "id" => $scopeItem->provider->id,
                            "source" => $scopeItem->provider->source,
                        ],
                        "shortName" => $scopeItem->shortName,
                        "telephoneActivated" => $scopeItem->getTelephoneActivated(),
                        "telephoneRequired" => $scopeItem->getTelephoneRequired(),
                        "customTextfieldActivated" => $scopeItem->getCustomTextfieldActivated(),
                        "customTextfieldRequired" => $scopeItem->getCustomTextfieldRequired(),
                        "customTextfieldLabel" => $scopeItem->getCustomTextfieldLabel(),
                        "captchaActivatedRequired" => $scopeItem->getCaptchaActivatedRequired(),
                    ];
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $notFoundIds[] = $scopeId;
            }
        }

        if (empty($scopes)) {
            $responseContent = [
                'scopes' => [],
                'error' => 'Scope(s) not found',
            ];
            $response = $response->withStatus(404)
                                 ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode($responseContent));
            return $response;
        }

        $responseContent = ['scopes' => $scopes];
        if (!empty($notFoundIds)) {
            $responseContent['warning'] = 'The following scopeId(s) were not found: ' . implode(', ', $notFoundIds);
        }

        $response = $response->withStatus(200)
                             ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write(json_encode($responseContent));
        return $response;
    }
}
