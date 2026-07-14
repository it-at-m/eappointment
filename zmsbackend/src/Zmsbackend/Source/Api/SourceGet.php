<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Source\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Source\Service\Source;

class SourceGet extends \BO\Zmsbackend\Api\BaseController
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
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $sourceData = (isset($args['source']) && $args['source'])
            ? (new \BO\Zmsbackend\Source\Service\Source())->readEntity($args['source'], $resolveReferences)
            : false;
        if (! $sourceData) {
            throw new \BO\Zmsbackend\Source\Exception\SourceNotFound();
        }
        $message->data = $sourceData;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
