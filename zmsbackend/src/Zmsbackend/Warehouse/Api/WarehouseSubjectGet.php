<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Warehouse\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;

class WarehouseSubjectGet extends \BO\Zmsbackend\Api\BaseController
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
        $subject = Validator::value($args['subject'])->isString()->getValue();
        $exchangeClass = '\BO\Zmsbackend\Exchange\Service\Exchange' . ucfirst($subject ?? '');
        if (! class_exists($exchangeClass)) {
            throw new \BO\Zmsbackend\Warehouse\Exception\UnknownReportType();
        }
        $subjectIdList = (new $exchangeClass())->readSubjectList();

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Helper\ExchangeAccessFilter($subjectIdList, $workstation))
          ->getFilteredEntity()
          ->withLessData();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
