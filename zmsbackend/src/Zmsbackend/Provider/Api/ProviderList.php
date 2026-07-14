<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Provider\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Provider\Service\Provider;

class ProviderList extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $isAssigned = Validator::param('isAssigned')->isBool()->getValue();
        $requestList = Validator::param('requestList')->isString()->getValue();

        $providerList = (new \BO\Zmsbackend\Provider\Service\Provider())->readListBySource(
            $args['source'],
            $resolveReferences,
            $isAssigned,
            $requestList
        );

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $providerList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
