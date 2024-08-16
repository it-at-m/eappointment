<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class OfficesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $scopeList = $sources->getScopeList();
        $providerProjectionList = [];

        foreach ($sources->getProviderList() as $provider) {
            $matchingScope = null;
            foreach ($scopeList as $scope) {
                if ($scope->provider->id == $provider->id) {
                    $matchingScope = $scope;
                    break;
                }
            }
            $providerData = [
                "id" => $provider->id,
                "name" => $provider->displayName ?? $provider->name,
            ];
            if ($matchingScope) {
                $providerData["scope"] = [
                    "id" => $matchingScope->id,
                    "provider" => $matchingScope->provider,
                    "shortName" => $matchingScope->shortName,
                    "telephoneActivated" => $matchingScope->getTelephoneActivated(),
                    "telephoneRequired" => $matchingScope->getTelephoneRequired(),
                    "customTextfieldActivated" => $matchingScope->getCustomTextfieldActivated(),
                    "customTextfieldRequired" => $matchingScope->getCustomTextfieldRequired(),
                    "customTextfieldLabel" => $matchingScope->getCustomTextfieldLabel(),
                    "captchaActivatedRequired" => $matchingScope->getCaptchaActivatedRequired()
                ];
            }
            
            $providerProjectionList[] = $providerData;
        }

        return Render::withJson($response, [
            "offices" => $providerProjectionList,
        ]);
    }
    
}
