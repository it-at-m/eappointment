<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Scope\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Scope\Service\Scope as Query;

class ScopeList extends \BO\Zmsbackend\Api\BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scopeList = (new Query())->readList($resolveReferences);
        if (0 == $scopeList->count()) {
            throw new \BO\Zmsbackend\Scope\Exception\ScopeNotFound(); // @codeCoverageIgnore
        }
        if ((new \BO\Zmsbackend\Helper\User($request))->hasLogin()) {
            (new \BO\Zmsbackend\Helper\User($request))->checkAnyPermission('restrictedscope', 'scope');

        } else {
            $scopeList = $scopeList->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $scopeList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
