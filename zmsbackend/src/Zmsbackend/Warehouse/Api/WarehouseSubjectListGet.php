<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Warehouse\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Warehouse\Service\Warehouse as Query;

class WarehouseSubjectListGet extends \BO\Zmsbackend\Api\BaseController
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
        $workstation = (new \BO\Zmsbackend\Helper\User($request, 2))->checkPermissions('statistic');

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $subjectsList = (new Query())->readSubjectsList();
        $message->data = (new \BO\Zmsbackend\Helper\ExchangeAccessFilter($subjectsList, $workstation))
          ->getFilteredEntity()
          ->withLessData();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
