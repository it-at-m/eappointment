<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;

class WarehousePeriodGet extends BaseController
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
        $workstation = (new Helper\User($request, 2))->checkRights('scope');
        $subject = Validator::value($args['subject'])->isString()->getValue();
        $subjectId = Validator::value($args['subjectId'])->isString()->getValue();
        $period = Validator::value($args['period'])->isString()->isBiggerThan(2)->setDefault('_')->getValue();
        $validator = $request->getAttribute('validator');
        $groupby = $validator->getParameter('groupby')->isString()->isBiggerThan(2)->getValue();

        $exchangeClass = '\BO\Zmsdb\Exchange' . ucfirst($subject);
        if (! class_exists($exchangeClass)) {
            throw new Exception\Warehouse\UnknownReportType();
        }
        $periodHelper = new Helper\ExchangePeriod($period);
        $subjectPeriod = (new $exchangeClass())->readEntity(
            $subjectId,
            $periodHelper->getStartDateTime(),
            $periodHelper->getEndDateTime(),
            $periodHelper->getPeriodIdentifier($groupby)
        );
        if (0 == count($subjectPeriod['data'])) {
            throw new Exception\Warehouse\ReportNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = (new Helper\ExchangeAccessFilter($subjectPeriod, $workstation))->getFilteredEntity();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
