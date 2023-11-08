<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

class WarehouseSubjectGet extends BaseController
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
        $exchangeClass = '\BO\Zmsdb\Exchange' . ucfirst($subject);
        if (! class_exists($exchangeClass)) {
            throw new Exception\Warehouse\UnknownReportType();
        }
        $subjectIdList = (new $exchangeClass)->readSubjectList();

        $message = Response\Message::create($request);
        $message->data = (new Helper\ExchangeAccessFilter($subjectIdList, $workstation))
          ->getFilteredEntity()
          ->withLessData();

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, $message->getStatuscode());
        return $response;
    }
}
