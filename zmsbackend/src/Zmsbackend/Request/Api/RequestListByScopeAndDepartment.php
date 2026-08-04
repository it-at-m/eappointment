<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Request\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;

/**
 * Scope requests plus department-wide additional requests for statistic collection (ZMSKVR-1431).
 */
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
        $grouped = (new \BO\Zmsbackend\Request\Service\Request())
            ->readListByScopeAndDepartment((int) $args['id'], (int) $resolveReferences);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $grouped;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}
