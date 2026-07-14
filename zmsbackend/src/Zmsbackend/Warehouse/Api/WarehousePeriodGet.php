<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Warehouse\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;

class WarehousePeriodGet extends \BO\Zmsbackend\Api\BaseController
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
        $subjectId = Validator::value($args['subjectId'])->isString()->getValue();
        $period = Validator::value($args['period'])->isString()->isBiggerThan(2)->setDefault('_')->getValue();
        $validator = $request->getAttribute('validator');
        $groupby = $validator->getParameter('groupby')->isString()->isBiggerThan(2)->getValue();
        $fromDate = $validator->getParameter('fromDate')->isString()->getValue();
        $toDate = $validator->getParameter('toDate')->isString()->getValue();

        $exchangeClass = '\BO\Zmsbackend\Exchange\Service\Exchange' . ucfirst($subject ?? '');
        if (! class_exists($exchangeClass)) {
            throw new \BO\Zmsbackend\Warehouse\Exception\UnknownReportType();
        }

        $periodHelper = new \BO\Zmsbackend\Helper\ExchangePeriod($period);
        $start = $periodHelper->getStartDateTime();
        $end = $periodHelper->getEndDateTime();

        $subjectPeriod = (new $exchangeClass())->readEntity(
            $subjectId,
            $fromDate ? new \DateTime($fromDate) : $start,
            $toDate ? new \DateTime($toDate) : $end,
            $periodHelper->getPeriodIdentifier($groupby)
        );
        if (0 == count($subjectPeriod['data'])) {
            throw new \BO\Zmsbackend\Warehouse\Exception\ReportNotFound();
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = (new \BO\Zmsbackend\Helper\ExchangeAccessFilter($subjectPeriod, $workstation))->getFilteredEntity();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
