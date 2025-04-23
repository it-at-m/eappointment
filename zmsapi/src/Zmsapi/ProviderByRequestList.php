<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\Provider;

class ProviderByRequestList extends BaseController
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
        if (! $args['csv']) {
            throw new Exception\Provider\RequestsMissed();
        }
        $providerList = (new Provider())->readListBySource($args['source'], $resolveReferences, null, $args['csv']);

        if (0 == $providerList->count()) {
            throw new Exception\Provider\ProviderNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $providerList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
