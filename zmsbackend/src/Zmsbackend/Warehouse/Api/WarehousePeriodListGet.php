<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Warehouse\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;

class WarehousePeriodListGet extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('statistic');
        $validator = $request->getAttribute('validator');
        $period = $validator->getParameter('period')->isString()->isBiggerThan(2)->setDefault('month')->getValue();
        $subject = Validator::value($args['subject'])->isString()->getValue();
        $subjectId = Validator::value($args['subjectId'])->isNumber()->getValue();
        $exchangeClass = '\BO\Zmsbackend\Exchange\Service\Exchange' . ucfirst($subject ?? '');
        if (! class_exists($exchangeClass)) {
            throw new \BO\Zmsbackend\Warehouse\Exception\UnknownReportType();
        }
        $subjectPeriodList = (new $exchangeClass())->readPeriodList($subjectId, $period);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $subjectPeriodList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
