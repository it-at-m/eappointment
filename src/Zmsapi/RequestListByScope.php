<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Request;

class RequestListByScope extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = (new \BO\Zmsdb\Scope)->readEntity($args['id'], $resolveReferences ? $resolveReferences : 1);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $requestList = (new Request)
            ->readListByProvider($scope->provider['source'], $scope->getProviderId(), $resolveReferences);

        $message = Response\Message::create($request);
        $message->data = $requestList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
