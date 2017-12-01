<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

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
        (new Helper\User($request))->checkRights('scope');
        $subject = Validator::value($args['subject'])->isString()->getValue();
        $subjectId = Validator::value($args['subjectId'])->isNumber()->getValue();
        $period = Validator::value($args['period'])->isString()->isBiggerThan(2)->setDefault('_')->getValue();

        $exchangeClass = '\BO\Zmsdb\Exchange' . ucfirst($subject);
        if (! class_exists($exchangeClass)) {
            throw new Exception\Warehouse\ReportNotFound();
        }

        $periodHelper = new Helper\ExchangePeriod($period);
        $subjectPeriod = (new $exchangeClass)->readEntity(
            $subjectId,
            $periodHelper->getStartDateTime(),
            $periodHelper->getEndDateTime(),
            $periodHelper->getPeriodIdentifier()
        );
        if (! $subjectPeriod) {
            throw new Exception\Warehouse\ReportNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $subjectPeriod;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
