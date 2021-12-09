<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;

class Pickup extends BaseController
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
        $workstation = (new Helper\User($request))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $limit = Validator::param('limit')->isNumber()->setDefault(1000)->getValue();
        $offset = Validator::param('offset')->isNumber()->setDefault(null)->getValue();
        $selectedScope = Validator::param('selectedScope')->isNumber()->getValue();
        $scopeId = ($selectedScope) ? $selectedScope : $workstation->scope['id'];
        $scope = (new \BO\Zmsdb\Scope)->readEntity($scopeId, 0);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $processList = (new Process)->readProcessListByScopeAndStatus(
            $scope['id'],
            'pending',
            $resolveReferences,
            $limit,
            $offset
        );

        $message = Response\Message::create($request);
        $message->data = $processList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
