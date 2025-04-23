<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process;
use BO\Zmsdb\Scope;

class ConflictListByScope extends BaseController
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
        (new Helper\User($request))->checkRights('basic');
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();

        $startDateFormatted = Validator::param('startDate')->isString()->getValue();
        $endDateFormatted = Validator::param('endDate')->isString()->getValue();
        $startDate = ($startDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($startDateFormatted) : \App::$now;
        $endDate = ($endDateFormatted) ? new \BO\Zmsentities\Helper\DateTime($endDateFormatted) : \App::$now;

        $scope = (new Scope())->readEntity($args['id'], 1);
        if (!$scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $conflictList = (new Process())->readConflictListByScopeAndTime(
            $scope,
            $startDate,
            $endDate,
            \App::$now,
            $resolveReferences
        );

        $message = Response\Message::create($request);
        $message->data = $conflictList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
