<?php

/**
 * @package ZMS API
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;

class ProcessListByExternalUserId extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions();
        $externalUserId = $args['externalUserId']; // present and validated because of URL argument
        $status = Validator::param('status')->isString()->setDefault(null)->getValue();
        $filterId = Validator::param('filterId')->isNumber()->setDefault(null)->getValue();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $limit = Validator::param('limit')->isNumber()->setDefault(100)->getValue();

        $processList = (new \BO\Zmsbackend\Process\Service\Process())->readProcessListByExternalUserId($externalUserId, $filterId, $status, $resolveReferences, $limit);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $processList->withAccess($workstation->getUseraccount());

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
