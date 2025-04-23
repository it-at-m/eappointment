<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope as Query;

class ScopeListByRequest extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $requestEntity = (new \BO\Zmsdb\Request())->readEntity($args['source'], $args['id']);
        if (! $requestEntity->hasId()) {
            throw new Exception\Request\RequestNotFound();
        }

        $scopeList = (new Query())->readByRequestId($requestEntity->getId(), $args['source'], $resolveReferences);
        if (! (new Helper\User($request))->hasRights() && ! Helper\User::hasXApiKey($request)) {
            $scopeList = $scopeList->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $scopeList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
