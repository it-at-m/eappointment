<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Scope\Service\Scope;

class ConflictListByScope extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('appointment');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();

        $startDateFormatted = Validator::param('startDate')->isString()->getValue();
        $endDateFormatted = Validator::param('endDate')->isString()->getValue();
        $startDate = ($startDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($startDateFormatted) : \App::$now;
        $endDate = ($endDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($endDateFormatted) : \App::$now;

        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($args['id'], 1);
        if (!$scope) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound();
        }

        $conflictList = (new \BO\Zmsbackend\Process\Service\Process())->readConflictListByScopeAndTime(
            $scope,
            $startDate,
            $endDate,
            \App::$now,
            $resolveReferences
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $conflictList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
