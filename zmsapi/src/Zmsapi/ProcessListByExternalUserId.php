<?php

/**
 * @package ZMS API
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Process;

class ProcessListByExternalUserId extends BaseController
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
        // TODO filter by status confirmed
        $workstation = (new Helper\User($request, 2))->checkRights();
        $externalUserId = $args['externalUserId'];
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $filterId = Validator::param('filterId')->isNumber()->getValue();
        $limit = Validator::param('limit')->isNumber()->setDefault(100)->getValue();

        $processList = (new Process())->readProcessListByExternalUserId($externalUserId, $filterId, $resolveReferences, $limit);

        $message = Response\Message::create($request);
        $message->data = $processList->withAccess($workstation->getUseraccount());

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
