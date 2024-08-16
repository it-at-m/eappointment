<?php

namespace BO\Zmscitizenapi;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class ScopesList extends BaseController
{
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $sources = \App::$http->readGetResult('/source/' . \App::$source_name . '/', [
            'resolveReferences' => 2,
        ])->getEntity();

        $scopeList = $sources->getScopeList() ?? [];
        $scopesProjectionList = [];

        foreach ($scopeList as $scope) {
            $scopesProjectionList[] = [
                "id" => $scope->id,
                "provider" => $scope->provider,
                "shortName" => $scope->shortName,
                "telephoneActivated" => $scope->getTelephoneActivated(),
                "telephoneRequired" => $scope->getTelephoneRequired(),
                "customTextfieldActivated" => $scope->getCustomTextfieldActivated(),
                "customTextfieldRequired" => $scope->getCustomTextfieldRequired(),
                "customTextfieldLabel" => $scope->getCustomTextfieldLabel(),
                "captchaActivatedRequired" => $scope->getCaptchaActivatedRequired()
            ];
        }

        return Render::withJson($response, [
            "scopes" => $scopesProjectionList,
        ]);
    }
}
