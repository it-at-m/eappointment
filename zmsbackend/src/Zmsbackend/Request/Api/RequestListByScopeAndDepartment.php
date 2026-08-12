<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Request\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Request\Service\Request as Query;
use BO\Zmsentities\Requeststatistic;

class RequestListByScopeAndDepartment extends \BO\Zmsbackend\Api\BaseController
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
        $grouped = (new Query())
            ->readListByScopeAndDepartment((int) $args['id'], (int) $resolveReferences);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = new Requeststatistic([
            'scopeRequests' => $grouped['scopeRequests'],
            'additionalDepartmentRequests' => $grouped['additionalDepartmentRequests'],
        ]);

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}
