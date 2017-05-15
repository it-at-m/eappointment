<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\DayOff as Query;

class DayoffList extends BaseController
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
        (new Helper\User($request))->checkRights();
        $yearRange = (new \DateTimeImmutable)->modify('+ 10years')->format('Y');
        if ($args['year'] > $yearRange) {
            throw new Exception\Dayoff\YearOutOfRange();
        }
        $dayOffList = (new Query())->readCommonByYear($args['year']);

        $message = Response\Message::create($request);
        $message->data = $dayOffList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
