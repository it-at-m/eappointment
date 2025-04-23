<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope;

class ScopeGet extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $message = Response\Message::create($request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $keepLessData = Validator::param('keepLessData')->isArray()->setDefault([])->getValue();
        $hasGQL = Validator::param('gql')->isString()->getValue();
        $accessRights = Validator::param('accessRights')->isString()->isBiggerThan(4)->setDefault('basic')->getValue();
        $getIsOpened = Validator::param('getIsOpened')->isNumber()->setDefault(0)->getValue();
        $scope = (new Scope())->readEntity($args['id'], $resolveReferences);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $userAccess = new Helper\User($request, 2);
        if ($userAccess->hasRights()) {
            $userAccess->checkRights(
                $accessRights,
                new \BO\Zmsentities\Useraccount\EntityAccess($scope)
            );
        } else {
            $scope = ($hasGQL) ? $scope : $scope->withLessData($keepLessData);
            $message->meta->reducedData = true;
        }

        if ($getIsOpened) {
            $scope->setStatusAvailability('isOpened', (new Scope())->readIsOpened($scope->getId(), \App::$now));
        }
        $message->data = $scope;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
