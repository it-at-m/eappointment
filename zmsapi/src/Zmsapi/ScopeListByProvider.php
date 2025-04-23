<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Scope as Query;

class ScopeListByProvider extends BaseController
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
        $provider = (new \BO\Zmsdb\Provider())->readEntity($args['source'], $args['id']);
        if (! $provider->hasId()) {
            throw new Exception\Provider\ProviderNotFound();
        }

        $scopeList = (new Query())->readByProviderId($provider->id, $resolveReferences);
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
