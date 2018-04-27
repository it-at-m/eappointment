<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

class WarehousePeriodListGet extends BaseController
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
        $validator = $request->getAttribute('validator');
        $period = $validator->getParameter('period')->isString()->isBiggerThan(2)->setDefault('month')->getValue();
        $subject = Validator::value($args['subject'])->isString()->getValue();
        $subjectId = Validator::value($args['subjectId'])->isNumber()->getValue();
        $exchangeClass = '\BO\Zmsdb\Exchange' . ucfirst($subject);
        if (! class_exists($exchangeClass)) {
            throw new Exception\Warehouse\ReportNotFound();
        }
        $subjectPeriodList = (new $exchangeClass)->readPeriodList($subjectId, $period);
        if (0 == count($subjectPeriodList['data'])) {
            throw new Exception\Warehouse\ReportNotFound();
        }

        $message = Response\Message::create($request);
        $message->data = $subjectPeriodList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
